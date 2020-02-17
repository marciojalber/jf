<?php

namespace JF\DB;

use JF\Config;
use JF\Exceptions\ErrorException;
use JF\Messager;

/**
 * Classe que representa um banco-de-dados.
 */
class DB
{
    /**
     * Instâncias de banco-de-dado.
     */
    protected static $instances = array();

    /**
     * Configuração de conexão do esquema.
     */
    protected $config           = null;

    /**
     * Nome do esquema de conexão do banco.
     */
    protected $schemaName       = null;

    /**
     * Statement da última consulta.
     */
    protected $pdo              = null;

    /**
     * Statement da última consulta.
     */
    protected $stmt             = null;

    /**
     * Resultado da última consulta.
     */
    protected $success          = null;

    /**
     * Opções da extração da consulta all.
     */
    protected $opts             = array();

    /**
     * Armazena os dados a serem utilizados na consulta.
     */
    protected $data             = array();

    /**
     * Previne a instanciação de bancos-de-dadosa.
     */
    protected function __construct()
    {

    }

    /**
     * Retorna uma instância do banco-de-dados.
     */
    public static function instance( $schema_name = 'main', $disable_exception = false )
    {
        // Se já existe conexão estabelecida
        if ( isset( static::$instances[ $schema_name ] ) )
        {
            $instance           = static::$instances[ $schema_name ];
            $instance->opts     = array();
            
            return $instance;
        }

        $instance               = new self();
        $instance->schemaName   = $schema_name;

        $env                    = ENV;
        $config                 = Config::get([
            "db/$env.schemas.$schema_name",
            "db/all.schemas.$schema_name"
        ]);

        // Retorna erro por não encontrar as configurações de conexão
        if ( !$config )
        {
            $msg                = Messager::get(
                'db',
                'missing_schema_config',
                $schema_name
            );

            if ( $disable_exception )
            {
                return null;
            }

            throw new ErrorException( $msg );
        }

        $instance->config       = $config;

        // Tenta capturar as configurações de conexão
        $dsn            =
            "{$config->driver}:host={$config->hostname};" .
            "dbname={$config->dbname};charset=utf8";

        try {
            $username   = $config->username;
            $password   = isset( $config->password )
                ? $config->password
                : null;
            $options    = array(
                \PDO::ATTR_EMULATE_PREPARES => false,
                \PDO::ATTR_PERSISTENT       => false,
            );
            $pdo        = new \PDO( $dsn, $username, $password, $options );
        }
        catch ( ErrorException $e )
        {
            $error      = $e->getMessage();
            $msg        = Messager::get(
                'db',
                'invalid_schema',
                $schema_name,
                $error
            );
            throw new ErrorException( $msg );
        }
        
        $instance->pdo                      = $pdo;
        self::$instances[ $schema_name ]    = $instance;
        
        return self::$instances[ $schema_name ];
    }

    /**
     * Retorna as configurações de conexão da instância.
     */
    public function config( $context = null )
    {
        if ( !$context )
        {
            return $this->config; 
        }

        return !empty( $this->config->$context )
            ? $this->config->$context
            : null;
    }

    /**
     * Retorna a instância PDO do objeto DB.
     */
    public function pdo()
    {
        return $this->pdo;
    }
    
    /**
     * Inicia uma transação.
     */
    public function transaction()
    {
        $this->pdo->beginTransaction();
        return $this;
    }

    /**
     * Cancela uma transação.
     */
    public function rollback()
    {
        $this->pdo->rollback();
        return $this;
    }

    /**
     * Conclui uma transação.
     */
    public function commit()
    {
        $this->pdo->commit();
        return $this;
    }

    /**
     * Limpa uma tabela no banco-de-dados.
     */
    public function truncate( $table )
    {
        $sql = "TRUNCATE TABLE `{$table}`";
        return $this->pdo()->query( $sql );
    }

    /**
     * Executa a SQL.
     */
    public function execute( $sql, array $data = array() )
    {            
        // Acerta os valores passados como ARRAY
        $this->data     = array();

        foreach ( $data as $key => $value )
        {
            self::fixDataQuery( $key, $value, $sql );
        }

        // Tenta executar a SQL
        $stmt           = $this->pdo->prepare( $sql );

        if ( !$stmt )
        {
            $error      = $this->pdo->errorInfo();
            $schema     = $this->schemaName;
            $hostname   = $this->config->hostname;
            $error      = $error[ 2 ] . " (host {$hostname}) - {$sql}";

            throw new ErrorException( $error );
        }
        
        // Guarda informações locais e retorna o objeto
        $this->success  = $stmt->execute( $this->data );
        $this->stmt     = $stmt;

        return $this;
    }

    /**
     * Acerta os dados não escalares em uma consulta.
     */
    private function fixDataQuery( $key, $value, &$sql )
    {
        if ( substr( $key, 0, 1 ) !== ':' )
        {
            $key = ':' . $key; 
        }


        if ( is_null( $value ) || is_scalar( $value ) )
        {
            $this->data[ $key ] = $value;
            return;
        }

        if ( is_resource( $value ) )
        {
            $msg    = Messager::get( 'db', 'when_using_resource_as_data_query' );
            throw new ErrorException( $msg );
        }

        $new_keys   = array();
        $values     = is_array( $value )
            ? $value
            : (array) $value;

        foreach ( $values as $row_value )
        {
            $new_key                = SQL::makeParam();
            $new_keys[]             = $new_key;
            $this->data[ $new_key ] = $row_value;
        }

        $new_keys   = implode( ', ', $new_keys );
        $sql        = str_replace( $key, $new_keys, $sql );
    }

    /**
     * Informa se a última operação foi bem-sucedida.
     */
    public function success()
    {
        return $this->success;
    }

    /**
     * Recupera o ID do último registro inserido.
     */
    public function insertId()
    {
        if ( !$this->success )
            return null;

        return $this->pdo->lastInsertId();
    }

    /**
     * Recupera a quantidade de registros afetados.
     */
    public function count()
    {
        return $this->stmt->rowCount();
    }

    /**
     * Define a instância de retorno de uma extração de dados.
     */
    public static function getFetchStyle( $opts )
    {
        if ( is_bool( $opts ) )
        {
            return \PDO::FETCH_ASSOC;
        }

        if ( isset( $opts[ 'class' ] ) && class_exists( $opts[ 'class' ] ) )
        {
            return $opts[ 'class' ];
        }

        $fetch = !empty( $opts[ 'object' ] )
            ? \PDO::FETCH_OBJ
            : \PDO::FETCH_ASSOC;

        return $fetch;
    }

    /**
     * Extrai a primeira linha da consulta.
     */
    public function one( $opts = [] )
    {
        $opts   = is_array( $opts )
            ? $opts
            : [];
        $opts   = array_merge( $opts, $this->opts );
        $fetch  = self::getFetchStyle( $opts );
        is_int( $fetch )
            ? $this->stmt->setFetchMode( $fetch )
            : $this->stmt->setFetchMode( \PDO::FETCH_CLASS, $fetch );
        $record = $this->stmt->fetch();

        if ( !$record )
            return null;

        if ( is_string( $fetch ) && isset( $opts[ 'class_start' ] ) )
        {
            $startMethod = $opts[ 'class_start' ];
            $record->$startMethod();
        }

        return $record;
    }

    /**
     * Extrai todas as linhas da consulta.
     */
    public function all( $opts = [] )
    {
        $opts           = is_array( $opts )
            ? $opts
            : [];
        $opts           = array_merge( $this->opts, $opts );

        // Captura os resultados
        $fetch          = self::getFetchStyle( $opts );
        $result         = is_int( $fetch )
            ? $this->stmt->fetchAll( $fetch )
            : $this->stmt->fetchAll( \PDO::FETCH_CLASS, $fetch );
        $response       = [];
        $has_index      = !empty( $opts[ 'index' ] );
        $index          = null;

        if ( !$result )
            return $response;

        if ( $has_index )
        {
            $index      = $opts[ 'index' ];
            $indexes    = array_keys( (array) current( $result ) );
        }
        
        if ( $has_index && !in_array( $index, $indexes ) )
        {
            $msg        = Messager::get( 'db', 'missing_informed_index', $index );
            throw new ErrorException( $msg );
        }
        
        foreach ( $result as $i => $record )
        {
            if ( is_string( $fetch ) && isset( $opts[ 'class_start' ] ) )
            {
                $startMethod = $opts[ 'class_start' ];
                $record->$startMethod();
            }

            $val_index = $i;

            if ( $index && is_array( $record ) )
                $val_index = $record[ $index ];

            if ( $index && is_object( $record ) )
                $val_index = $record->$index;

            $response[ $val_index ]   = $record;
        }

        return $response;
    }

    /**
     * Define uma chave para indexar os resultados.
     */
    public function indexBy( $key = null )
    {
        $this->opts[ 'index' ] = $key;
        return $this;
    }

    /**
     * Exibe o último erro ocorrido nas consultas.
     */
    public function lastError()
    {
        $error = $this->stmt->errorInfo();
        return $error[2];
    }
}

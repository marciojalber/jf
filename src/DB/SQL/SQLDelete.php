<?php

namespace JF\DB\SQL;

use JF\DB\DB;

/**
 * Trait para executar a exclusão de registros.
 */
class SQLDelete extends SQLBuilder
{
    use SQL_MakeParam;

    use SQL_From;
    use SQL_Where;
    use SQL_Offset;
    use SQL_Limit;

    /**
     * Método construtor.
     */
    public function __construct( $dto = null )
    {
        $this->dto = $dto;

        if ( $dto )
            $this->from( $dto::table() );
    }

    /**
     * Constrói a SQL.
     */
    public function sql()
    {
        list( $where, $data )   = $this->getWhere();
        $offset                 = $this->getOffset();
        $limit                  = $this->getLimit();
        $sql                    = "DELETE FROM `$this->table` WHERE $where";

        if ( $offset )
            $sql   .= ' OFFSET ' . $offset;

        if ( $limit )
            $sql   .= ' LIMIT ' . $limit;

        return (object) [
            'action'    => 'delete',
            'sql'       => $sql,
            'data'      => $data,
        ];
    }

    /**
     * Retorna todos os registros da operação.
     */
    public function count()
    {
        $dto    = $this->dto;
        $sql    = $this->sql();
        $total  = DB::instance( $dto::schema() )
            ->execute( $sql->sql, $sql->data )
            ->count();

        return $total;
    }
}

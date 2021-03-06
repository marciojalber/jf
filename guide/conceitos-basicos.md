INTRODUÇÃO
==========


Por que JF Framework?
---------------------

No meu trabalho no INSS, houve um momento em equipe que meu chefe e eu decidimos
padronizar o uso de apenas um framework, para melhorar a produtividade da equipe.
Elegemos alguns critérios, fizemos pesquisas e testamos os frameworks de mercado.

A maioria deles atendia muito bem aos critérios que elegemos (documentação farta,
variedade de componentes nativos e plugins, MVC e ORM), mas falhavam em diversos
outros aspectos (instalação ou configuração complexa, código não intuitivo,
ações repetitivas não automatizadas, muito código PHP nas views).

Então decidi criar meu próprio framework, mesmo diante da recomendação contrária de
quase todos os meus colegas, quando elegi novos critérios:

1.  Instalação sem dependências.
2.  Configurações sem utilizar funções ou métodos, apenas arrays.
3.  Configurações bem distribuídas em arquivos com poucos subníveis e
    com nomes intuitivos.
3.  Ações repetitivas automatizadas, reduzindo linhas de código.
4.  Nomes intuitivos dos componentes e métodos com foco em sua utilidade.
5.  Arquivos dos componentes fáceis de localizar a partir do seu nome e namespace.
6.  Códigos dos componentes do framework simples e bem comentados, facilitando seu
    aprendizado a partir do estudo direto do código-fonte.
7.  Maior automatização no tratamento de dados pelos models.
8.  Pouca interferência do PHP para gerar documentos HTML ou seus componentes.
9.  Adoção do padrão REST.
10. Melhor distribuição de responsabilidades nas classes e camadas.

Com esses critérios, procurei atingir os seguintes objetivos:

1. Reduzir ao mínimo o tempo entre o início e a publicação de uma nova aplicação.
2. Reduzir ao mínimo o tempo de integração de novos membros à equipe de desenvolvimento.
3. Reduzir ao mínimo a necessidade de codificação.
4. Possibilitar aplicações de alto desempenho.
5. Oferecer maior autonomia aos frameworks frontend na construção das views.


Arquitetura
-----------

O framework adota uma arquitetura própria de camadas, próxima à conhecida HMVC
(Hierarquical Model View and Controller). Porém com algumas camadas a mais,
melhorando a distribuição de códigos pelas suas responsabilidades:

- *Config*
    Armazena as configurações da aplicação.

- *Controllers*:
    Recebe as requisições HTML / outros formatos.
    Executa o controle de acessos (método before).
    Prepara os dados de layout - método beforeView (nas requisições HTML).
    Solicita dados ou execução de serviços ao domínio (classes do Domain).
    Define os dados de página - método view_[nome_da_action] (nas requisições HTML).
    Retorna os dados - método service_[nome_da_action] (nas requisições não HTML).

- *Domain*
    Núcleo da execução dos processos e regras de negócio.

- *Helpers*
    Classes auxiliares produzidas pelo time da aplicação para formatação de dados
    isolados e para execução de serviços comuns da aplicação, tais formatação de datas,
    envio de e-mail, manipulação personalizada de arquivos, etc.

- *Models*
    Camada responsável por comunicar diretamente com as tabelas.
    Aqui também devem conter os códigos SQL ou geradores de consultas personalizadas.

- *Rules*
    Armazena as regras de negocio que serão utilizadas pelos Domains.

- *Types*
    Utilizado pelos models para validação e filtro automático de dados.

- *Views* (somente requisições HTML):
    Armazena arquivos de layout (/layouts).
    Armazena arquivos de página (/pages).
    Armazena fragmentos de página (/partials).
    Armazena páginas de erro (/errors).

- *Vendors*
    São as bibliotecas e APIs de terceiros.


Documentação e aprendizado
--------------------------

A documentação foi feita para desenvolvedores que já têm algum nível de maturidade.
Sua simplificidade busca reduzir a quantidade de leitura e aumentar a velocidade de
aprendizado.

O roteiro da documentação é a seguinte:

1.  INTRODUÇÃO (jf/guides/intro.txt)
    1.1. Por que JF Framework?
    1.2. Arquitetura
    1.3. Documentação e aprendizado

2.  PRIMEIROS PASSOS (jf/guides/primeiros-passos.txt)
    2.1. Iniciando uma nova aplicação
    2.2. Estrutura de pastas e arquivos
    2.3. Configuração da aplicação

3.  REQUISIÇÕES-HTTP (jf/guides/requisicoes.txt)
    3.1. Introdução
    3.2. Rotas
    3.3. Idioma da requisição
    3.4. Formatos de resposta
    3.5. Enviando dados pela URL
    3.6. Como manipular as requisições

4.  CONTROLADORES (jf/guides/controladores.txt)
    4.1. Introdução
    4.2. Estrutura dos controladores
    4.3. Alterando o tipo de resposta da requisição
    4.4. Acessando dados enviados nas requisições
    4.5. Processamento de dados
    4.6. Retornando dados em HTML
    4.7. Retornando dados em outros formatos

5.  MONTANDO PÁGINAS HTML (jf/guides/paginas-html.txt)
    5.1. Layout de página
    5.2. Utilizando dados na página
    5.3. Injetando conteúdo HTML
    5.4. Injetando arquivos CSS e JS atualizados
    5.5. Injetando URL em outro idioma
    5.6. Injetando propriedades dos models

6.  LINHA DE COMANDOS (jf/guides/linha-de-comandos.txt)
    6.1. Introdução
    6.2. Execução de um comando
    6.2. Definção dos comandos

7.  DOMÍNIO DA APLICAÇÃO (jf/guides/dominio.txt)
    7. Introdução

8.  MANIPULANDO CONFIGURAÇÕES (jf/guides/manipulando-config.txt)
    8.1. Invocar configurações
    8.2. Alterar configurações

9.  MANIPULANDO BANCOS-DE-DADOS (jf/guides/manipulando-bd.txt)

10. MANIPULANDO SESSÕES (jf/guides/mainpulando-sessoes.txt)
    8.1. Introdução
    8.2. Métodos da classe JF\Session

11. MANIPULANDO CACHE (jf/guides/manipulando-cache.txt)

12. MANIPULANDO ARQUIVOS E PASTAS (jf/guides/manipulando-arq-pastas.txt)
    12.1. Dir
        clear( $directory )
        listItems( $path )
        getItems( $path, $asObject = false, $recursive = false )
        getFiles( $path, $asObject = false, $recursive = false )
        getDirs( $path, $asObject = false, $recursive = false )
    12.2. FTP
        getMimeType( $filename )
        addPharFiles( \Phar &$phar, $itens )
        open( $filename )
        setMap( array $map = array() )
        getCsv( array $conditions = [], $separator = ';', $enclosure = '"' )
    12.3. FTP
        connect( $schema = 'main' )
        put( $file_source, $path_target = '', $file_target_name = null )

13. DOCUMENTAÇÃO DA APLICAÇÃO (jf/guides/documentacao.txt)
    12.1. Introdução
    12.2. Tags gerais
    12.2. Tags de classes
    12.2. Tags de métodos

ANEXO I - CONSTANTES (jf/guides/anexo1-constantes.txt)
ANEXO II - BOAS PRÁTICAS (jf/guides/boas-praticas.txt)
    II.1. Introdução
    II.2. PSRs
    II.3. Boas práticas para nomeação de elementos
    II.4. Boas práticas para comentários
    II.5. Boas práticas para codificação


// FALTA
// Controladores
    // Descrever o método catch
// Manipulando arquivos
// Manipulando bancos-de-dados
// Manipulando cache
// Prioridades - reavaliar
    // 1. Resultado
    // 2. Segurança
    // 3. Código limpo
    // 4. Produtividade
    // 5. Desempenho
    // 6. Flexibilidade


SEGURANÇA

    IMPLEMENTADO
    Htaccess acesso arquivos
    URL não acessa arquivos
    URL não executa Shell
    Senhas cript antes do banco
    SQL injection x prepare
    Http_only
    Http_trans_id
    Senha não cookies
    Session_name
    Xss com vue
    validar dados no servidor
    Senhas não arquivo
    Http_user_agent
    Senhas letras números e símbolos
    Chave de nova aplicação sessões
    Session_handler log não salva sess

    FALTA
    Display errors só dev
    Testes
    Http_refer + token forms
    Session_regenerate_id
    3 Tentativas login
    Captcha
    Permissoes pastas e arquivos
    Registrar tentativas de login
    Ssl
    Bin2hex
    Desabilitar erros em produção
    Generalizar mensagens de erro

DESEMPENHO

    IMPLEMENTADO
    Análise URL
    Não usar templates páginas
    Require *_once não usa
    Caminhos absolutos
    Não usa switch
    SQL com limite 1
    Não usar @ suprimir erros
    Echo x print

    DICAS
    Menos ternario
    Pré incremento
    Comparador identidade
    Aspas simples
    Não usar função em loop
    Strlen x string{pós}
    Não utilizar variáveis globais
    Pré inicializar variáveis
    Unset
    strcmp e str_compare x strtolower
    Preferência por métodos estáticos
    Não utilizar métodos mágicos
    Não redimensionar IMG html
    Sprites
    Junte os CSS e os Scripts
    IMG x base64
    Select * não usar
    Chamar método na classe filha
    CSS no topo e js no fim

    NÃO IMPLEMENTA
    não uso de htaccess
    Não evita Poo x array
    Ob_start( 'ob_gzhandler' )

    FALTA
    Minificar CSS e js
    sprite imagens
    Cache
    Não utiliza DocBlock
    Session_save_path( DIR_SESS/N )
    Desabilitar erros em produção

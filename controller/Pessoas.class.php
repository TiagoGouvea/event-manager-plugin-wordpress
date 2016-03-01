<?php
use lib\SmartResize;
use TiagoGouvea\PLib;

/**
 * User: TiagoGouvea
 * Date: 02/08/14
 * Time: 14:36
 */
class ControllerPessoas
{

    public static function dispatcher()
    {
        Eventos::getInstance()->init();

        $action = $_GET['action'];
        $id = $_GET['id'];
        $registro = null;
        if ($id != null)
            $registro = Pessoas::getInstance()->getById($id);

        if ($action == null)
            self::showList();
        if ($action == 'add-new' || $action == 'edit')
            self::showForm($action, $registro);
        if ($action == 'add-extra')
            self::showFormAddExtra($registro);
        if ($action == 'delete')
            self::delete($registro);
        if ($action == 'view')
            self::view($registro);
        if ($action == 'extras')
            self::extras();
        if ($action == 'genderize')
            self::genderize();
    }

    public static function view($pessoa)
    {
        require_once PLUGINPATH . '/view/pessoas/view.php';
    }

    public static function showList($filter = null)
    {
        require_once PLUGINPATH . '/view/pessoas/list.php';

        $pessoas = Pessoas::getInstance()->getAll();

//        foreach ($pessoas as $pessoa) {
//            /* @var $pessoa Pessoa */
//            $pessoa->setExtra('mailchimp_sync', 'Id Mailchimp', 'l');
//            Pessoas::getInstance()->save($pessoa->id, $pessoa);
//        }

        $subTitulo = "Todos as Pessoas já inscritas";

        ListTablePessoas($pessoas, $subTitulo, $filter);
    }

    public static function showForm($action, $pessoa = null)
    {
        // Postando?
        if (count($_POST) > 0) {
            // Validar
            /* @var $pessoa Pessoa */
            $pessoa = Pessoas::getInstance()->populate($_POST, $pessoa);

            if ($_POST['observacoes']!='')
                $pessoa->setExtra('observacoes', 'Observações', $_POST['observacoes']);
            if ($_POST['minibio']!='')
                $pessoa->setExtra('minibio', 'Mini Bio', $_POST['minibio']);
            foreach(Pessoas::$networksGreat as $networkKey=>$networkTitle){
                if ($_POST[$networkKey]!='') {
//                    var_dump($networkKey);
//                    var_dump($networkTitle);
//                    var_dump($_POST[$networkKey]);
                    $pessoa->setExtra($networkKey, $networkTitle, $_POST[$networkKey]);
                }
            }

            $pessoa->nome = $_POST['nome'];

            // Salvar ou incluir?
            if ($_POST['id'] == null) {
                $pessoa = Pessoas::getInstance()->insert($pessoa);
            } else {
                $pessoa = Pessoas::getInstance()->save($_POST['id'], $pessoa);
            }

            if ($pessoa && $_FILES['arquivo'] && $_FILES['arquivo']['tmp_name']!='') {
                ControllerPessoas::updatePicture($pessoa,$_FILES['arquivo']);
            }


            self::view($pessoa);
        } else {
            require_once PLUGINPATH . '/view/pessoas/form.php';
        }
    }

    private static function updatePicture($pessoa, $origem)
    {
        if (strtolower(strpos($origem['name'], '.png')) === false)
            return false;

        // Destinos
        $destino = ABSPATH.'/imagens/pessoa/'.$pessoa->id.'.png';
        $miniatura = ABSPATH.'/imagens/pessoa/'.$pessoa->id.'_200x200.png';

//        var_dump($origem);
//        var_dump($destino);
        // Se existirem, apagar
        if (file_exists($destino))
            unlink($destino);
        if (file_exists($miniatura))
            unlink($miniatura);
        // Salvar original convertida em png

        move_uploaded_file($origem['tmp_name'],$destino);
        return ControllerPessoas::createMiniatura($destino,$miniatura, 200,200,80);
    }

    public static function atualizarPerfil()
    {
        // Recebendo post?
        if (!is_autenticado() || count($_POST) == 0)
            return;

        $pessoa = get_the_pessoa();
        if ($pessoa->id != $_POST['id_pessoa']) return;

        $pessoa->setExtra('minibio', 'Mini Bio', $_POST['minibio']);
        $pessoa->setExtra('facebook', 'Facebook', $_POST['facebook']);
        $pessoa->setExtra('twitter', 'Twitter', $_POST['twitter']);
        $pessoa->setExtra('linkedin', 'Linkedin', $_POST['linkedin']);
        $pessoa->setExtra('gplus', 'Google +', $_POST['gplus']);
        $pessoa->setExtra('instagram', 'Instagram', $_POST['instagram']);
        $pessoa->setExtra('pinterest', 'Pinterest', $_POST['pinterest']);
        $pessoa->setExtra('skype', 'Skype', $_POST['skype']);
        $pessoa->setExtra('github', 'GitHub', $_POST['github']);
        $pessoa->setExtra('site', 'Site', $_POST['site']);
        $pessoa->setExtra('empresa', 'Empresa/Faculdade', $_POST['empresa']);
        $pessoa->setExtra('cargo', 'Cargo/Curso', $_POST['cargo']);
        Pessoas::getInstance()->save($pessoa->id, $pessoa);

        if (count($_FILES)>0){
            $file = $_FILES[0];
            // Arquivos
            $sucesso = ControllerPessoas::updatePicture($pessoa,$file);
            if ($sucesso)
                echo json_encode(array("sucesso"=>true));
            else
                echo json_encode(array("sucesso"=>false));
            die();
        }

        setFlash("sucesso");
    }

    private static function showFormAddExtra(Pessoa $pessoa)
    {
        // Postando?
        if (count($_POST) > 0) {
            // Validar
            $chave = $_POST['chave'];
            $titulo = $_POST['titulo'];
            $valor = $_POST['valor'];
            if ($chave != null && $valor != null) {
                /* @var $pessoa Pessoa */
                $pessoa = Pessoas::getInstance()->getById($_POST['id']);
                $pessoa->setExtra($chave, $titulo, $valor);
                $pessoa->save();
                self::view($pessoa);
            } else {
                require_once PLUGINPATH . '/view/pessoas/form_extra.php';
            }
        } else {
            require_once PLUGINPATH . '/view/pessoas/form_extra.php';
        }
    }

    private static function extras()
    {
        // Exibe os extras e pessoas
        $indices = array();
        $valores = array();

        // Obter todas pessoas
        $pessoas = Pessoas::getInstance()->getAll();

        /* @var $pessoa Pessoa */
        foreach ($pessoas as $pessoa) {
            if ($pessoa->extras == null) continue;

            $extras = (Array)json_decode($pessoa->extras);
            foreach ($extras as $extraIndice => $extra) {
                $extra = (array)$extra;
                if (!isset($indices[$extraIndice])) {
                    $indices[$extraIndice] = array();
                    $indices[$extraIndice]['titulo'] = $extra['titulo'];
                    $indices[$extraIndice]['valores'] = array();
                    if (strpos($extra['titulo'], '[ ]') > 0)
                        $indices[$extraIndice]['tipo'] = 'bool';
                    else
                        $indices[$extraIndice]['tipo'] = '';
                }
                if (strpos($extra['titulo'], '[ ]') > 0) {
                    if ($extra['valor'] == '1')
                        $extra['valor'] = 'Sim';
                    else
                        $extra['valor'] = 'Não';
                }
                $valor = str_replace("<br>", null, $extra['valor']);
                $conteudo = "<a href=''>$pessoa->nome</a> ($pessoa->email) " . $valor . "<br>";

//                if (strpos($extra['titulo'], '[')>0) {
                if ($valores[$valor] == null) $valores[$valor] = 0;
                $valores[$valor]++;
//                }

                $indices[$extraIndice]['valores'][] = $conteudo;
            }

        }

        //foreach($valores as $valor=>$qtd){
        //    echo $valor." = ".$qtd."<br>";
        //}

        echo "<br><br><h2>Extras</h2>";

        echo "<table width='100%'>";
        echo "<tr><td>Extra</td><td>Respostas</td><td>Tipo</td></tr>";
        foreach ($indices as $k => $indice) {
            echo "<tr>";
            echo "<td><a href='#indice_{$k}'>" . $indice['titulo'] . "</a></td>";
            echo "<td>" . count($indice['valores']) . "</td>";
            echo "<td>" . $indice['tipo'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";

        echo "<hr><br><br><h2>Respostas</h2>";

        foreach ($indices as $k => $indice) {
            echo "<h2><a name='indice_{$k}'>" . $indice['titulo'] . "</a></h2>";
            echo "Registros: " . count($indice['valores']) . "<br><br>";
            $return = null;
            foreach ($indice['valores'] as $valor) {
                $return .= $valor;
            }

            echo $return;
        }
    }

    function contains($string, Array $search, $caseInsensitive = true)
    {
        $string = $string . " ";
        $string = str_replace("  ", " ", $string);
        $string = str_replace("  ", " ", $string);
        $exp = '/'
            . implode(' |^', array_map('preg_quote', $search))
            . ($caseInsensitive ? '/i' : '/');
        //$exp = str_replace("$|", "|", $exp);
        //var_dump($string);
        //var_dump($exp);die();
        return preg_match($exp, $string) ? true : false;
    }


    function removeAccents($name)
    {
        $unwanted_array = array('Š' => 'S', 'š' => 's', 'Ž' => 'Z', 'ž' => 'z', 'À' => 'A', 'Á' => 'A', 'Â' => 'A', 'Ã' => 'A', 'Ä' => 'A', 'Å' => 'A', 'Æ' => 'A', 'Ç' => 'C', 'È' => 'E', 'É' => 'E',
            'Ê' => 'E', 'Ë' => 'E', 'Ì' => 'I', 'Í' => 'I', 'Î' => 'I', 'Ï' => 'I', 'Ñ' => 'N', 'Ò' => 'O', 'Ó' => 'O', 'Ô' => 'O', 'Õ' => 'O', 'Ö' => 'O', 'Ø' => 'O', 'Ù' => 'U',
            'Ú' => 'U', 'Û' => 'U', 'Ü' => 'U', 'Ý' => 'Y', 'Þ' => 'B', 'ß' => 'Ss', 'à' => 'a', 'á' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a', 'å' => 'a', 'æ' => 'a', 'ç' => 'c',
            'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e', 'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i', 'ð' => 'o', 'ñ' => 'n', 'ò' => 'o', 'ó' => 'o', 'ô' => 'o', 'õ' => 'o',
            'ö' => 'o', 'ø' => 'o', 'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'ý' => 'y', 'ý' => 'y', 'þ' => 'b', 'ÿ' => 'y');
        return strtr($name, $unwanted_array);
    }




    public static function recuperarSenha()
    {
        // Recebendo post de autenticação?
        $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
        $pessoa = Pessoas::getInstance()->getByEmail($email);
        if ($pessoa) {
            // Enviar email
            /* @var $organizador Organizador */
            $organizadores = Organizadores::getInstance()->getAll();
            $organizador = $organizadores[0];
            $mensagem = Mensagens::getInstance()->get('email_lembrete_senha', null, $pessoa);
            $organizador->enviarEmail(
                $pessoa->email,
                "Lembrete de Senha",
                $mensagem
            );
            setFlash("Acabamos de lhe enviar um email com seu lembrete de senha. Se isso não for o suficiente entre em contato conosco.");
        } else {
            setFlashError("Nenhum usuário encontrado com este email.");
            return false;
        }
    }

    public static function autenticar()
    {
        // Recebendo post de autenticação?
        $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
        $password = $_POST['password'];
        $pessoa = self::validarAutenticar($email, $password);
        if ($pessoa) {
            // Registrar em sessão
            if (!session_id())
                session_start();
            $_SESSION['id_pessoa'] = $pessoa->id;
            $_SESSION['autenticado'] = true;
        } else {
            //echo getFlashErrorString();
        }
//        var_dump($_POST);
    }

    private static function validarAutenticar($email, $password)
    {
//        var_dump($_POST);
        if ($email == '' || $password == '') {
            setFlashError("Informe o email e a senha de acesso.");
            return false;
        }

        // Obter pessoa por email
        $pessoa = Pessoas::getInstance()->getByEmail($email);
        if ($pessoa == null) {
            setFlashError("Não te encontramos com o email informado. Certifique-se de ter digitado o email corretamente.");
            return false;
        }

//        var_dump($pessoa);

        // Validar senha
        if ($password != $pessoa->password) {
            setFlashError("Senha inválida.");
            return false;
        }

        return $pessoa;
    }


    public static function logout()
    {
        if (session_id() == null)
            session_start();
        session_destroy();
    }

    public static function createMiniatura($origem, $destino, $width, $height, $qualidade)
    {
        // Criar miniatura
        return SmartResize::resize($origem,null,$width,$height,false,'file',$destino,false,false,$qualidade);
    }

    private static function genderize()
    {

        $menNames = array('felipe', 'diego', 'rodrigo', 'alexandre', 'pedro', 'marcelo', 'paulo', 'victor', 'andré', 'lucas', 'alex', 'fernando', 'henrique', 'bernardo', 'marcio', 'sandro', 'eduardo', 'joão', 'joao', 'igor', 'marcio', 'leonardo', 'daniel', 'carlos', 'rafael', 'pedro', 'tiago', 'bruno', 'victor', 'ricardo', 'wallace', 'fernando', 'matheus', 'gustavo', 'vinicius', 'anderson', 'renan', 'arthur', 'samuel', 'mário', 'mateus', 'marcos', 'guilherme', 'fábio', 'jorge', 'jonathan', 'flavio', 'rogério', 'marco', 'andre', 'rogério', 'gabriel', 'andré', 'thiago', 'marquinhos', 'luís', 'raphael', 'leandro', 'renato', 'diogo', 'vitor', 'caio', 'junior', 'alan', 'roberto', 'fabio', 'filipe', 'allan', 'adriano', 'alberto', 'alexandre', 'vinícius', 'sergio', 'leo', 'hugo', 'douglas', 'yuri', 'denis', 'breno', 'pablo', 'edson', 'robson', 'léo', 'fred', 'thales', 'wesley', 'michel', 'jefferson', 'erick', 'ramon', 'mauro', 'marcello', 'mauricio', 'murilo', 'beto', 'artur', 'romulo', 'wellington', 'fabricio', 'luan', 'fabrício', 'yago', 'eric', 'marlon', 'davi', 'julio', 'maurício', 'yan', 'brunno', 'frederico', 'nando', 'miguel', 'italo', 'roger', 'jackson', 'luca', 'lukas', 'alef', 'dyego', 'ian', 'bruce', 'elvis', 'icaro', 'dodô', 'otavio', 'maicon', 'jefferson', 'milton', 'jaime', 'brian', 'william', 'humberto', 'erik', 'márcio', 'alvaro', 'rick', 'maycon', 'walter', 'rubens', 'kim', 'augusto', 'wagner', 'george', 'neto', 'robert', 'oscar', 'emerson', 'edu', 'joel', 'dudu', 'dan', 'luciano', 'ygor', 'raul', 'marcel', 'celso', 'danilo', 'gui', 'jason', 'ivan', 'claudio', 'cesar', 'fabiano', 'mike', 'michael', 'jean', 'juan', 'john', 'david', 'juninho', 'albert', 'luiz', 'gilmar', 'matthew', 'francisco', 'christiano', 'ronaldo', 'philip', 'cleber', 'antonio', 'marcus', 'patrick', 'cristiano', 'rodolfo', 'evandro', 'everton', 'elias', 'higor', 'everton', 'charles', 'juliano', 'willian', 'christian', 'felippe', 'saulo', 'richard', 'alisson', 'sergio', 'guto', 'gilberto', 'lucio', 'fillipe', 'dr.', 'angelo', 'emanuel', 'joaquim', 'tony', 'joaquim', 'reinaldo', 'fagner', 'brenno', 'paulinho', 'nicholas', 'joe', 'jonatas', 'silvio', 'helio', 'eder', 'manoel', 'nathan', 'gil', 'giovanni', 'justin', 'jeferson', 'andrew', 'kiko', 'adam', 'lucca', 'cristian', 'issac', 'jonas', 'cassio', 'matt', 'will', 'caique', 'matt', 'chico', 'luccas', 'sebastian', 'heitor', 'dominique', 'paul', 'elton', 'natan', 'arnaldo', 'isaac', 'marcelinho', 'henry', 'regis', 'alexander', 'ronan', 'ewerton', 'derek', 'clayton', 'ricky', 'markk', 'tom', 'fellipe', 'wilson', 'wanderson', 'johnny', 'thomas', 'nick', 'fellipe', 'ruan', 'nicolas', 'bernard', 'steven', 'geraldo', 'vicente', 'ulisses', 'enzo', 'anthony', 'rennan', 'rob', 'jairo', 'alejandro', 'romario', 'alfredo', 'emmanuel', 'jair', 'ralph', 'raoni', 'vini', 'cadu', 'franklin', 'javier', 'jack', 'greg', 'giovani', 'ronald', 'armando', 'lincoln', 'magno', 'iago', 'wanderson', 'ismael', 'jeff', 'vagner', 'israel', 'rodolpho', 'kaio', 'darlan', 'kadu', 'cleiton', 'phelipe', 'edgar', 'thomaz', 'zeca', 'philippe', 'fabinho', 'cleiton', 'alessandro', 'luis', 'rogerio', 'nelson', 'mario', 'atila', 'andrey', 'moises', 'pietro', 'darlan', 'lucca', 'thomaz', 'james', 'jack', 'magno', 'savio', 'lukas', 'enzo', 'regis', 'kevin', 'iury', 'philipe', 'ari', 'ulysses', 'jhon', 'samir', 'octavio', 'bispo', 'pastor','nicollas', 'marllon','mazio','lesley','leonir', 'leonando', 'lenon','kassio', 'kyro','josias', 'jhonathan','jhonatan','jeronimo','jaques','inacio','glauber', 'hemerson','gilson', 'gabriell', 'denner', 'romero', 'eliton', 'maxwell', 'thalles', 'mendel', 'nailson', 'ederson', 'romeu', 'allison', 'jadir', 'bryan', 'ari', 'abner', 'thomaz', 'kevin', 'luigi', 'edgar', 'pierre', 'atila', 'joshua', 'edilson', 'samir', 'denilson', 'zeca', 'wendell', 'phelipe', 'andrei', 'valmir', 'thor', 'tales', 'aaron', 'rony', 'alexsandro', 'guga', 'vanderson', 'cezar', 'reginaldo', 'hudson', 'francesco', 'dennis', 'thyago', 'rui', 'ailton', 'doug', 'jordan', 'jon', 'deivid', 'valter', 'everson', 'betinho', 'tomas', 'jorginho', 'kayo', 'tyler', 'isaias', 'osmar', 'hamilton', 'ignacio', 'nilson', 'alison', 'natanael', 'marquinho', 'ivo', 'orlando', 'tadeu', 'frank', 'hilton', 'dario', 'paolo', 'osvaldo', 'ezequiel', 'plinio', 'omar', 'helder', 'sidney', 'herbert', 'murillo', 'helder', 'maykon', 'edgar', 'hiago', 'wilsinho', 'kelvin', 'adilson', 'lipe', 'franco', 'jhonny', 'mark', 'andy', 'bob', 'kaique', 'ciro', 'jay', 'tito', 'ton', 'martin', 'toni', 'otto', 'francis', 'ruy', 'josue', 'geovane', 'marko', 'esteban', 'silas', 'silas', 'rubem', 'braulio', 'edgard', 'xande', 'tarcisio', 'iuri', 'ezequiel', 'theo', 'wendell', 'wendel', 'valter', 'santiago', 'ryan', 'braulio', 'andres', 'tarcisio', 'haroldo', 'brandon', 'thierry', 'jansen', 'nikolas', 'vinny', 'bill', 'joca', 'mikael', 'jovander', 'mika', 'walber', 'caaio', 'carloss', 'heber', 'carlo', 'mildo', 'charlie', 'hohniie', 'xandao', 'benedito', 'billy', 'charlie', 'dieguinho', 'dimitri', 'gustavo', 'edmilson', 'scott', 'dimitri', 'stefano', 'cleyton', 'andrezinho', 'coutinho', 'alexsander', 'bradd', 'ricardinho', 'andrezinho', 'rico', 'johnnie', 'gusttavo', 'gerardo', 'brad', 'lorenzo', 'isac', 'ziggy', 'ernane', 'jader', 'edinho', 'steve', 'tayro', 'ernane', 'sander', 'danillo', 'hebert', 'washington','ronnie', 'moreno', 'oswaldo', 'ramiro', 'peterson', 'durval', 'hyago', 'alyssson', 'emanoel', 'thulio', 'toninho', 'edin', 'wanderlei', 'mathias', 'danyllo', 'boris', 'cydney', 'luiscarlos', 'elisson', 'jonathas', 'wemerson', 'claudinei', 'leon', 'camilo', 'guy', 'serginho', 'gabryel', 'cleverson', 'cacaco', 'ademar', 'affonso', 'marcilio', 'jhony', 'alysson', 'carlinhos', 'messias', 'mariano', 'jerfferson', 'johnatan', 'tassio', 'renatinho', 'aelson', 'olavo', 'jimmy', 'julian', 'amaral', 'cliff', 'carl', 'carlosedu', 'emiliano', 'valentin', 'marc', 'amaral', 'ken', 'edney', 'junnior', 'fael', 'adalton', 'kaua', 'muriel', 'gean', 'jesus', 'marvin', 'diangelo', 'vitinho', 'osama', 'heverton', 'valerio', 'amauri', 'armandinho', 'jerry', 'matteus', 'dodo', 'tomaz', 'phillip', 'helinho', 'luke', 'cassio', 'psicologo', 'contador', 'adilio','warley','sillas','sebastiao','reiny','jhonata','giordano','fausto','daves','welington');
        $girlNames = array('luiza', 'vanessa', 'alice', 'gleyse', 'natália', 'elisa', 'larissa', 'rachel', 'anna', 'roberta', 'clarissa', 'sheila', 'marina', 'michelle', 'carla', 'cris', 'bárbara', 'miriam', 'viviane', 'thaís', 'bruna', 'priscila', 'laura', 'sabrina', 'barbara', 'alessandra', 'marcia', 'dani', 'patrícia', 'lara', 'karina', 'sarah', 'mari', 'nathalia', 'kelly', 'karla', 'paloma', 'pri', 'andreia', 'natalia',  'carolyne', 'carolaine', 'sandra', 'gisele', 'jessica', 'paulinha', 'rita', 'marcella', 'monica', 'jaqueline', 'monica', 'mônica', 'isadora', 'monique', 'lívia', 'lorena', 'andréa', 'andréia', 'alinne', 'eduarda', 'natalya', 'alexandra', 'adriana', 'josi', 'ana', 'angelica', 'cláudia', 'alyssa', 'debora', 'priscilla', 'camilla', 'babi', 'milena', 'victória', 'tatiane', 'joyce', 'karol', 'mary', 'laís', 'rose', 'paola', 'michele', 'daniela', 'caroline', 'karen', 'izabela', 'talita', 'thalita', 'cintia', 'mayara', 'valéria', 'flavia', 'carina', 'aline', 'nathália', 'letícia', 'leticia', 'simone', 'denise', 'marcela', 'helena', 'valeria', 'valdete', 'manuela', 'camila', 'amanda', 'marcela', 'camila', 'gabrielle', 'luana', 'érika', 'erica', 'érica', 'erika', 'mariana', 'fernanda', 'nayara', 'bia', 'beatriz', 'patricia', 'fernanda', 'bianca', 'carol', 'ana', 'flávia', 'silvia', 'gabi', 'stephanie', 'regina', 'marta', 'samantha', 'maíra', 'veronica', 'vera', 'flavinha', 'eliane', 'sofia', 'luma', 'sonia', 'cibele', 'kátia', 'maria', 'thais', 'carol', 'débora', 'isabel', 'jéssica', 'renata', 'giuliana', 'juliana', 'luíza', 'carolina', 'paty', 'elaine', 'lilian', 'manu', 'thamires', 'andressa', 'julia', 'júlia', 'aninha', 'isa', 'dany', 'jane', 'liz', 'taty', 'evelyn', 'nat', 'irene', 'paula', 'andrea', 'karine', 'raquel', 'karolina', 'vitória', 'daiane', 'marcelle', 'rayane', 'samara', 'fran', 'mel', 'rosangela', 'sthepanie', 'mayra', 'rayssa', 'ariane', 'suelen', 'thamiris', 'andrezza', 'agatha', 'angélica', 'alyne', 'adriane', 'luana', 'mila', 'yasmin', 'fabi', 'grazi', 'janine', 'bella', 'thays', 'naiara', 'milene', 'giulia', 'duda', 'natasha', 'brenda', 'vivian', 'nicole', 'pamela', 'stepanie', 'rebeca', 'lais', 'tati', 'sara', 'nanda', 'anne', 'nina', 'raíssa', 'lari', 'bru', 'louise', 'tata', 'tamara', 'kátia', 'jennifer', 'taís', 'marília', 'janaína', 'dayane', 'bel', 'nara', 'emanuelle', 'tainá', 'taiane', 'christiane', 'iara', 'gaby', 'ester', 'catarina', 'yasmim', 'valentina', 'ligia', 'malu', 'maiara', 'olivia', 'sophia', 'luciana', 'teresa', 'maira', 'deise', 'fabíola', 'martha', 'elizabeth', 'manuella', 'isis', 'luh', 'ariel', 'lili', 'yara', 'emily', 'claudia', 'vivi', 'paolla', 'beth', 'maysa', 'regiane', 'gabriela', 'cidinha', 'katia', 'cristiane', 'karlla', 'nathy', 'elisângela', 'líliam', 'verônica', 'kamilla', 'danyelle', 'taíse', 'lorraine', 'rahimara', 'valkiria', 'kelie', 'edna', 'gislaine', 'melissa', 'rhaiza', 'olga', 'ingrid', 'danielle', 'danny', 'gi', 'victoria', 'fatima', 'cassia', 'deborah', 'daniele', 'carine', 'raiane', 'tereza', 'pâmela', 'ruth', 'rosane', 'iris', 'cecília', 'livia', 'esther', 'mylena', 'lígia', 'giselle', 'rafaella', 'joice', 'soraya', 'sylvia', 'raphaella', 'virginia', 'catherine', 'betina', 'virgínia', 'danielly', 'rhayssa', 'rafaela', 'isabela', 'carolzinha', 'liliane', 'giovanna', 'evelyne', 'taynara', 'thainá', 'angela', 'pollyanna', 'thamara', 'rosana', 'ariella', 'isabella', 'gabriella', 'larah', 'tatiana', 'fabiana', 'luisa', 'mari', 'clara', 'dra', 'joana', 'isabelle', 'cristina', 'mariane', 'raphaela', 'diana', 'marianna', 'kamila', 'tamires', 'isabel', 'giovana', 'stella', 'lucia', 'suellen', 'silvana', 'suzana', 'karoline', 'heloisa', 'glaucia', 'tania', 'andreza', 'karoline', 'luciane', 'manoela', 'thaiane', 'fabiane', 'ludmila', 'eliana', 'julianna', 'poliana', 'rebecca', 'luanna', 'juliane', 'daiana', 'ellen', 'daniella', 'mariah', 'jacqueline', 'lidiane', 'alana', 'vivi', 'dayana', 'cinthia', 'wanessa', 'fabi', 'georgia', 'mirella', 'camille', 'pamella', 'nathalie', 'thaynara', 'brunna', 'joanna', 'clarice', 'izabel', 'mara', 'nicolle', 'leila', 'layla', 'hanna', 'alexia', 'ananda', 'jana', 'gabriele', 'rayanne', 'melina', 'liana', 'helen', 'francine', 'dandara', 'jordana', 'lorenna', 'cynthia', 'ivana', 'samanta', 'rejane', 'thayna', 'vania', 'josy', 'tahisa', 'estela', 'thereza', 'celia', 'gloria', 'kenia', 'geovana', 'franciane', 'alexsandra', 'thaissa', 'susana', 'lidia', 'alicia', 'marisa', 'celia', 'taissa', 'ursula', 'eva', 'raisa', 'bebel', 'carmem', 'ludmilla', 'stephany', 'marlene', 'dalila', 'jamile', 'thati', 'eliza', 'lays', 'julie', 'jussara', 'marcele', 'geovanna', 'nadia', 'natalie', 'eliza', 'marie', 'nadia', 'myllena', 'fabricia', 'thamyres', 'eliza', 'ariana', 'christina', 'jeniffer', 'eveline', 'alline', 'magda', 'dora', 'thaiana', 'shirley', 'geisa', 'claudinha', 'thalyta', 'talitha', 'millena', 'dayara', 'nath', 'maristela', 'katherine', 'dona', 'lis', 'juju', 'samira', 'rayana', 'gabrielly', 'gleice', 'lola', 'naiana', 'cida', 'leilane', 'elisabete', 'juh', 'lia', 'raissa', 'laryssa', 'carmen', 'hellen', 'izabella', 'laila', 'tainara', 'thayane', 'antonia', 'hannah', 'dra.', 'andresa', 'pati', 'emilia', 'shirley', 'lena', 'lilia', 'thalia', 'nati', 'julliana', 'grazielle', 'stefany', 'thaysa', 'leda', 'anita', 'marjorie', 'joelma', 'jessika', 'elis', 'jamille', 'thayana', 'mirelle', 'pollyana', 'gigi', 'leandra', 'catharina', 'eva', 'josi', 'magali', 'graziela', 'cristiana', 'luciene', 'tainara', 'gilmara', 'luna', 'naty', 'lana', 'josiane', 'lina', 'solange', 'elen', 'solange', 'sthefany', 'gisela', 'elena', 'dayanne', 'mariela', 'taina', 'cecilia', 'jana', 'mirian', 'emily', 'jade', 'mel', 'lis', 'anny', 'bru', 'tamiris', 'thaisa', 'sylvia', 'irene', 'jessyca', 'keila', 'bebel', 'valquiria', 'lorrana', 'laisa', 'denize', 'iasmin', 'morgana', 'karolyna', 'marianne', 'jheinnifer', 'lene', 'cassiana', 'helaina', 'nayra', 'karita', 'monaliza', 'rebeka', 'suelly', 'nalu', 'lina', 'elena', 'lorrayne', 'ticiana', 'thatiana', 'isabele', 'lily', 'ines', 'lizandra', 'jesse', 'elen', 'valquiria', 'naty', 'gabs', 'carlinha', 'marilene', 'renatinha', 'selma', 'jeane', 'patty', 'gessica', 'tiffany', 'milla', 'janice', 'iasmin', 'thayanne', 'evelin', 'ludimila', 'suzane', 'celina', 'pietra', 'jully', 'cami', 'diandra', 'branca', 'margarida', 'tita', 'allana', 'janete', 'celina', 'johanna', 'germana', 'thuany', 'crystal', 'stefanie', 'thamyris', 'raysa', 'desiree', 'vilma', 'suely', 'tayane', 'camile', 'margot', 'marii', 'emilly', 'rosiane', 'fernandinha', 'polyanna', 'vanusa', 'moema', 'marcelly', 'giullia', 'dri', 'waleska', 'elizete', 'izadora', 'tina', 'cinthya', 'lavinia', 'natacha', 'pilar', 'thaysa', 'luanda', 'silmara', 'maryana', 'talyta', 'marcelli', 'dara', 'eliete', 'rosemary', 'katharine', 'maya', 'glauce', 'angelina', 'sheyla', 'silmara', 'laiz', 'norma', 'telma', 'deia', 'margareth', 'maryana', 'july', 'nani', 'nice', 'lucilene', 'simony', 'iza', 'tathiana', 'marly', 'tayna', 'layane', 'belle', 'val', 'ane', 'anitta', 'beatrice', 'leidiane', 'edivania', 'thaila', 'thaty', 'marilia', 'thainara', 'thaina', 'lauren', 'cibelle', 'thayza', 'ligiane', 'palloma', 'alejandra', 'thaynnara', 'rayanee', 'rafaelle', 'lyudmilla', 'thainara', 'rosalia', 'mayah', 'mellize', 'jessyka', 'jaquelyne', 'graciele', 'jaquelina', 'rosalba', 'suzete', 'iabela', 'grendha', 'thayara', 'jessie', 'lila', 'daphne', 'graciela', 'jackeline', 'stefani', 'neide', 'monalisa', 'pat', 'iolanda', 'jackeline', 'evellyn', 'samyra', 'francisca', 'iza', 'junia', 'donna', 'celina', 'eunice', 'nathalya', 'ingridy', 'marise', 'valesca', 'viviana', 'iolanda', 'francisca', 'pat', 'nathalya', 'viviana', 'loren', 'ines', 'graca', 'carolline', 'joseane', 'jaque', 'loren', 'ludimila', 'emanuele', 'charlote', 'natasja', 'camyla', 'gabielle', 'brunninha', 'poli', 'sueli', 'thifany', 'ariadne', 'scheila', 'cassiane', 'veruschka', 'claryce', 'marcilene', 'mirele', 'aretha', 'cristhine', 'yana', 'marize', 'sulamara', 'suza', 'miriane', 'scarlett', 'carlah', 'carlarna', 'ivone', 'carlly', 'isaa', 'dandynha', 'nayana', 'catia', 'giseli', 'catarine', 'analu', 'mislene', 'suh', 'amandinha', 'ceci', 'josane', 'lyvia', 'tainah', 'priscylla', 'maryam', 'sabina', 'neila', 'micheline', 'michela', 'prisci', 'drica', 'kristina', 'jessy', 'ritinha', 'carolis', 'claudiane', 'nana', 'annelise', 'laise', 'luane', 'claudiane', 'sandy', 'darlene', 'bete', 'danibua', 'nubia', 'grasi', 'heloa', 'tayana', 'roseane', 'thamy', 'penelope', 'danubia', 'natali', 'tauane', 'natila', 'shayanne', 'fabielle', 'greice', 'polyana', 'cristine', 'fabiola', 'shayanne', 'shaya', 'fabielle', 'tayana', 'annita', 'tayrah', 'manoella', 'herica', 'luanne', 'manoella', 'cissa', 'kellen', 'tamyres', 'noemi', 'rosymar', 'rosimar', 'claudete', 'tayssa', 'mandy', 'aparecida', 'jamilly', 'michelly', 'micaella', 'sally', 'camylla', 'iasmim', 'candice', 'jocelia', 'charlotte', 'ashley', 'lourdes', 'helo', 'michaela', 'tais', 'drika', 'poliany', 'dania', 'simara', 'eleonora', 'leonora', 'lyandra', 'emilene', 'soraia', 'vanda', 'jasmine', 'tathiane', 'rute', 'denyse', 'carollina', 'yuly', 'crislaine', 'elza', 'rayanna', 'cassya', 'carlitha', 'jenifer', 'bibi', 'albina', 'leisa', 'gabryeli', 'leidyane', 'mirela', 'greyce', 'jady', 'naira', 'danielli', 'anelise', 'liandra', 'guta', 'betania', 'biah', 'jady', 'carinna', 'thairine', 'yasmini', 'stela', 'bya', 'diane', 'natalia', 'queli', 'rosilene', 'lanna', 'yulia', 'loriane', 'samarys', 'carole', 'tatah', 'evelien', 'cyntia', 'nizia', 'gizelly', 'diane', 'carolinne', 'rossana', 'ivanna', 'rosilene', 'gabii', 'susane', 'lucila', 'quel', 'karolline', 'ludy', 'cristal', 'ayala', 'kaisa', 'sacha', 'jakeline', 'meline', 'ianara', 'teka', 'ivonete', 'kenya', 'carly', 'tatielle', 'meline', 'katya', 'quelly', 'kendra', 'meire', 'ayla', 'poly', 'layza', 'yngrid', 'mana', 'suzanne', 'agata', 'meire', 'tatianni', 'paulina', 'clarinha', 'micheli', 'talitinha', 'constanza', 'estrella', 'evely', 'micha', 'michely', 'anelize', 'xanda', 'yolanda', 'luany', 'grazy', 'estefani', 'shirlei', 'josefina', 'imyra', 'giorgia', 'nilzete', 'eriika', 'giselia', 'tamyris', 'pitty', 'cleia', 'liziane', 'lucinha', 'ursulla', 'micaelle', 'lala', 'norah', 'leonor', 'heloiza', 'yanina', 'leona', 'iorane', 'tammy', 'janeth', 'clarisse', 'naany', 'mercia', 'brendha', 'neyde', 'tayrine', 'estela', 'pricila', 'monara', 'bianka', 'pillar', 'thatiane', 'caroliny', 'isabely', 'jaine', 'contadora', 'pscicologa', 'fotografa', 'bernadete', 'kamile', 'dalcilene','francila','gessyelle','inaiara','jerusa','marluce','marciliana','marcilia','miria','odirene','walkiria','thassya','taise','silvane','sammy','rosiene','raiza','lessandra','clemilda','andra','anapaula','stephania');


        $pessoas = Pessoas::getInstance()->getAll();

        $m = 0;
        $f = 0;
        $u = 0;
        $unkonw = array();
        $females = array();
        /* @var $pessoa Pessoa */
        foreach ($pessoas as $pessoa) {
            $fullname = trim(preg_replace("/[^\s\w\p{L} A-Za-z0-9.]/u", " ", $pessoa->primeiro_nome()));
            $fullname = self::removeAccents($fullname);
            $fullname = explode(" ", $fullname);
            $fullname = $fullname[0];
            $gender = null;

            // Try by name
            if (strlen($fullname) >= 3) {
                if (self::contains($fullname, $menNames)) {
                    $gender = "Masculino";
                    $m++;
                } elseif (self::contains($fullname, $girlNames)) {
                    $gender = "Feminino";
                    $f++;
                    $females[]=$pessoa;
                } else {
                    $u++;
                    $unkonw[] = $fullname;
                }

//                echo "$fullname - $gender<br>";

                //if ($gender!=null && ($pessoa->getExtra('gender')===null || $pessoa->getExtra('gender')!=$gender)){
                $pessoa->setExtra('gender', 'Gender', $gender);
                $pessoa->save();
//                    Echo "updated<br>";
                //}
            }

//            die();
        }
        echo "<h1>Resultado</h1>";
        echo "<br><b>Homens: $m<br>Mulheres: $f<br>Desconhecido: $u</b><br><br>";

        echo "<h1>Mulheres</h1>";
        foreach($females as $female){
            echo htmlentities("$female->nome <$female->email>") . "<br>";
        }

        echo "<h1>Desconhecidos</h1><pre>";
        var_dump($unkonw);
        echo "</pre>";
        die("genderize");

    }

}
<?php
/**
 * Created by PhpStorm.
 * User: TiagoGouvea
 * Date: 21/03/15
 * Time: 09:55
 */

class Integracao extends \TiagoGouvea\WPDataMapper\WPSimpleMapper {
    public $id;
    public $titulo;
    public $url;
    public $client; // pode ser email, client_id.. depende do serviço
    public $token;
    public $servico;
    public $_someThing;

    /**
     * @param $chave
     * @return StdClass
     */
    public function getPessoa($chave){
        $url = $this->url;
        $url = str_replace("{identificacao}",$chave,$url);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL,$url);
        $result=curl_exec($ch);
        curl_close($ch);

        //var_dump($result);die();
        if ($result!=null){
            $pessoaIntegracao = json_decode($result, true);

//            echo "<pre>";
//            var_dump($pessoaIntegracao);
//            die();
            // Tentar "traduzir" campos padrões
            $pessoa = new Pessoa();
            $pessoa->nome = $this->encontrarDado($pessoaIntegracao,"nome");
            $pessoa->email = $this->encontrarDado($pessoaIntegracao,"email");
            $pessoa->celular = $this->encontrarDado($pessoaIntegracao,"celular");
            // Todos os demais entrar como extras?
            foreach($pessoaIntegracao as $campo=>$valor)
                if ($valor!=null)
                    $pessoa->setExtra($campo,$campo,$valor);
//
//            echo "<pre>";
//            var_dump($pessoa);
//            die();

            return $pessoa;
        }
    }

    private function encontrarDado($valores,$dado){
        // Valor padrão
        if ($valores[$dado]!=null)
            return $valores[$dado];
        // Primeira Maiuscula
        if ($valores[ucfirst($dado)]!=null)
            return $valores[ucfirst($dado)];
        // Tudo maiusculo
        if ($valores[strtoupper($dado)]!=null)
            return $valores[strtoupper($dado)];
        // Sair em busca
        foreach($valores as $chave=>$valor){
            $chaveLower = strtolower($chave);
            if (strpos($chaveLower,$dado)!==false)
                return $valor;
        }
    }
}
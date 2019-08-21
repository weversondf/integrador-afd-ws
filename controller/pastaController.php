<?php

require_once "PdoPastas.php";

Class pastaController Extends baseController
{

    public function cargaAnistiados(){

    }

    public function index()
    {
        require_once "PdoPastas.php";

        $pastas = new PdoPastas();

        $log = $pastas->selectLog("2016-07-17 17:47:56.828217", '35201000000009');

        $format = null;

        $error = array();

        $url = 'https://afd.planejamento.gov.br/sei/controlador_ws.php?servico=sei';

        $cliente = new SoapClient($url, array('trace' => true));
        //die('<pre>'.print_r($log,1));
        foreach ($log as $key => $value) {
            //die('<pre>'.print_r($value,1));
            try {
                $ProtocoloFormatado = $value->nu_matricula_siape;
                $OrgaoUpag = '35201000000009';
                $SiglaSistema = 'IPF@AFD';
                $IdentificacaoServico = 'ServicoIPF@AFD';

                $result = $cliente->movimentarProcedimento($SiglaSistema, $IdentificacaoServico, $OrgaoUpag, $ProtocoloFormatado);

                $error[]["OK"] = $result;
            } catch (SoapFault $e) {
                $error[]["error"] = $e;
            }
        }
        die('<pre>' . print_r($error, 1));
    }

    private function wsDeletarProcedimento()
    {
        $pastas = new PdoPastas();
        $log = $pastas->selectLog($_POST['data'], $_POST['upag']);
        // die('<pre>'.print_r($log,1));
        $error = array();
        $url = $_POST['webservice'];
        // die('<pre>'.print_r($url,1));
        $cliente = new SoapClient($url, array('trace' => true));
        foreach ($log as $key => $value) {
            try {
                $ProtocoloFormatado = $value->nu_matricula_siape;

                $SiglaSistema = $_POST['sigla_sistema'];
                $IdentificacaoServico = $_POST['identificacao_servico'];
                $result = $cliente->deletarProcedimento($SiglaSistema, $IdentificacaoServico, $ProtocoloFormatado);

                $error[]["OK"] = $result;
            } catch (SoapFault $e) {
                $error[]["error"] = $e;
            }
        }
        die('<pre>' . print_r($error, 1));
    }

    public function selecionarPastasLog()
    {
        $pastas = new PdoPastas();
        return  $pastas->selectLogAfd($_POST['upag']);
    }

    public function deletar()
    {
        /*** set a template variable ***/
        $this->registry->template->title = 'Integrador AFD - Webservice - Serviço: deletarPasta';
        $this->registry->template->pastas = $this->validadePostDeletar();
        //die('<pre>'.print_r($this->validadePostDeletar(),1));
        $this->registry->template->wspastas = $this->wsValidadePostDeletar();
        $this->registry->template->post = $_POST;
        /*** load the index template ***/
        $this->registry->template->show('pasta/index');
    }

    private function validadePostDeletar()
    {
        if (isset($_POST['deletarProcedimento'])) {
            if(!empty($_POST['upag']))
                return $this->selecionarPastasLog($_POST);
        }else
        return 'Preencha os campos!';
    }

    private function wsValidadePostDeletar()
    {
        if (isset($_POST['wsDeletarProcedimento'])) {
            if(!empty($_POST['webservice']) && !empty($_POST['sigla_sistema']) && !empty($_POST['identificacao_servico']))
                return $this->wsDeletarProcedimento($_POST);
        }else
            return 'Preencha os campos!';
    }
}

?>

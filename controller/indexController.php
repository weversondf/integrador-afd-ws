<?php
ini_set('display_errors',1);
ini_set('display_startup_erros',1);
error_reporting(E_ALL);
date_default_timezone_set('America/Sao_Paulo');

// die('<pre>'.print_r($_POST,1));
Class indexController Extends baseController
{

    public function index()
    {
        $this->getPost();

        /*** set a template variable ***/
        $this->registry->template->title = 'Integrador AFD - Webservice - Serviço: incluirPasta';
        /*** load the index template ***/
        $this->registry->template->show('index');
    }

    private function getPost()
    {

        if (isset($_POST['iniciarProcedimento'])) {

            require_once "PdoPastas.php";

            $class = new PdoPastas();

            // $cliente = new SoapClient($_POST['webservice'], array('trace' => true));
			if ($_POST['webservice'] == 'servidor219') {
				$wsdl = "http://10.209.9.219/sei/controlador_ws.php?servico=sei";
			} else {
                $wsdl = "https://afd.planejamento.gov.br/sei/controlador_ws.php?servico=sei";
			}
            $cliente = new SoapClient($wsdl, array('trace' => true));

            $result = array();

            $upag = array();

			//die('<pre>' . print_r($_POST, 1));
						
            if ($_POST['carga'] == 'servidores') {
                $pastas = $class->selectPastasPorUpag($_POST['regime'], $_POST['upag']);	
			}
			if ($_POST['carga'] == 'servidores-intervalo') {
                $pastas = $class->selectPastas($_POST['regime'], $_POST['range_inicio'], $_POST['range_fim'], $_POST['upag']);
			}
			if ($_POST['carga'] == 'servidores-orgao') {
                $pastas = $class->selectPastasPorOrgao($_POST['regime'], $_POST['range_inicio'], $_POST['range_fim'], $_POST['orgao']);
			}
			if ($_POST['carga'] == 'instituidores') {
                // $pastas = $class->selectPastasInstituidores($_POST['range_inicio'], $_POST['range_fim'], $_POST['upag']);
                $pastas = $class->selectPastasInstituidores($_POST['regime'], $_POST['upag']);	
			}
			if (($_POST['carga'] == 'instituidores-incremental')) {
                $pastas = $class->selectPastasInstituidoresIncremental();
			}
            if ($_POST['carga'] == 'anistiados') {
                $pastas = $class->selectPastasAnistiados($_POST['range_inicio'], $_POST['range_fim'], $_POST['upag']);
                $anistiados_upag = '20113000056846';
                // $anistiados_upag = $_POST['upag'];
            }

            $OrgaoUpag = null;

            //print('<pre>' . print_r($pastas, 1));

            if (!empty($pastas)) {

                foreach ($pastas as $data) {

                    try {
                        $SiglaSistema = $_POST['sigla_sistema'];
                        $IdentificacaoServico = $_POST['identificacao_servico'];

                        if ($_POST['carga'] == 'anistiados') {
                            $ProtocoloFormatado = $this->removeSpecialCharacters($data->siape);
                            $OrgaoUpag = $anistiados_upag;
                            $DataAutuacao = $this->removeSpace(implode("-", array_reverse(explode("/", $data->data))));
                            $Especificação = $this->removeSpecialCharacters($data->cpf);//CPF do servidor
                            $Interessados = $data->nome;// nome do servidor
                        } elseif ($_POST['carga'] == 'instituidores') {
							// Serviço incluirPasta
                            $ProtocoloFormatado = $data->co_orgao_matr;
                            $OrgaoUpag = $data->nu_orgao_upag;
                            $DataAutuacao = $data->da_ocor_ingr_orgao_serv;
                            $Especificação = $data->nu_cpf; // CPF do servidor
                            $Interessados = $data->no_servidor; // nome do servidor 
							
							// Serviço incluirServidor
							// $SiglaUnidade      = $data->sigla_unidade;
							// $TipoAssentamento  = $data->tipo_assentamento;
							// $Interessado       = $data->interessado;
							// $CPF               = $data->cpf;
							// $DataIngressoCargo = $data->data_ingresso_cargo;
							// $Assentamento      = $data->assentamento;							
                        } else {
							// Serviço incluirPasta
                            $ProtocoloFormatado = $data->matricula_siape;
                            $OrgaoUpag = $data->nu_orgao_upag;
                            $DataAutuacao = $data->da_ocor_ingr_orgao_serv;
                            $Especificação = $data->nu_cpf; // CPF do servidor
                            $Interessados = $data->no_servidor; // nome do servidor 
							
							// Serviço incluirServidor
							// $SiglaUnidade      = $data->sigla_unidade;
							// $TipoAssentamento  = $data->tipo_assentamento;
							// $Interessado       = $data->interessado;
							// $CPF               = $data->cpf;
							// $DataIngressoCargo = $data->data_ingresso_cargo;
							// $Assentamento      = $data->assentamento;
                        }

                        //print('<pre>' . print_r($ProtocoloFormatado, 1));

                        //print('<pre>'.print_r($DataAutuacao,1));
						// Serviço incluirPasta
                        $TipoProcedimento = $data->regime_afd; // Corrigido! O valor vem da tabela de carga
                        $Assuntos = 701; // 1. - Provisão da Força de Trabalho
                        $NivelAcesso = 1; //restrito
                        $result[$ProtocoloFormatado] = $cliente->incluirPasta($SiglaSistema, $IdentificacaoServico, $OrgaoUpag, $ProtocoloFormatado, $DataAutuacao, $TipoProcedimento, $Especificação, $Assuntos, $Interessados, $NivelAcesso);
						
						// Regime e situação no log
						// $RegimeSituacao = $data->regime_afd .'-'.$data->sg_regime_situacao;
						
						// Serviço incluirServidor
                        // $result[$Assentamento] = $cliente->incluirServidor($SiglaSistema, $IdentificacaoServico, $SiglaUnidade, $TipoAssentamento, $Interessado, $CPF, $DataIngressoCargo, $Assentamento);

                        // print('<pre>'.print_r($result,1));
                        //$class->updatePastas($data->id_servidor);

                    } catch (SoapFault $e) {

                        $result[$ProtocoloFormatado] = $e;
                        // $result[$Assentamento] = $e;

                    }
                }

            } else {
                // $result = 'O regime informado está incorreto';
                $result = '<h3>Não há registros para carga incremental!</h3>';
				die(print_r($result, 1));
            }
            // die('<pre>' . print_r($result, 1));

            $values = array();
			foreach ($result as $key => $data){
				// die('<pre>' . print_r($data, 1));
				
				// Serviço incluirPasta
				$values[] = array(
					'co_orgao_matr'=>$key,
					'resposta_ws'=>$data,
					'dt_inclusao_reg'=>date('Y-m-d H:i:s')
				);
				// Serviço incluirServidor
				// $values[] = array(
					// 'assentamento'=>$key,
					// 'codigo_retorno'=>$data[0]->CodigoRetorno,
					// 'descricao'=>$data[0]->Descricao
				// );			
			}
			// die('<pre>' . print_r($values, 1));
            $class->insertLogPastas('tb_log_incluir_pasta',$values);
            // $class->insertLogPastas('tb_log_incluir_servidor', $values);
			
			// Não recarregar o form após o $POST, somente o resultado após o consumo do serviço iniciarProcedimento.
			exit();
        }
    }

    public function removeSpecialCharacters($insert)
    {
        $map = array(
            '.' => '',
            '-' => '',
            ' ' => ''
        );

        $word = strtr($insert, $map);

        return $word;
    }

    public function removeSpace($insert)
    {
        $map = array(
            ' ' => ''
        );

        $word = strtr($insert, $map);

        return $word;
    }

}

?>

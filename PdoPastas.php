<?php
/**
 * Created by PhpStorm.
 * User: D�rio
 * Date: 3/9/2016
 * Time: 2:54 PM
 */

// Forçar exibir erros do PHP
// ini_set("display_errors",1);
// ini_set("display_startup_erros",1);
// error_reporting(E_ALL);

// Resolve o erro PHP Fatal error: Allowed memory size of...
ini_set('memory_limit', '1024M');

header('Content-Type: text/html; charset=utf-8');

class PdoPastas
{
    /**
     * Método que monta a conexao com o banco de réplica do AFD de produção
     * @return pdoAfd
     */
	public function setPDOAfd()
    {
        try {
            $pdoAfd = new PDO('mysql:host=10.209.42.80;dbname=sei', 'leitura', 'leituraafd');
            $pdoAfd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException  $e) {
            echo "Error: " . $e;
        }
        return $pdoAfd;
    }
	
    public function selectLogAfd($upag)
    {
        $result = array();
        try {
			$sql = "SELECT protocolo.id_unidade_geradora, protocolo.protocolo_formatado as nu_matricula_siape, protocolo.descricao as nu_cpf
                    FROM protocolo 
                    JOIN unidade ON protocolo.id_unidade_geradora = unidade.id_unidade
                    WHERE sigla = '{$upag}';";

            $consulta = $this->setPDOAfd()->query($sql);
            while ($linha = $consulta->fetch(PDO::FETCH_OBJ)) {
                $result[] = $linha;
            }
            // die('<pre>'.print_r($result,1));
            return $result;
        } catch (Exception $e) {
            if($e->getCode() == '22007')
                return 'data invalida';
            die('<pre>' . print_r($e->getCode(), 1));
        }
    }	
	
    /**
     * m�todo que monta a conexao com o banco
     * @return PDO
     */
    public function setPDO()
    {
        try {
            //$pdo = new PDO('mysql:host=localhost;dbname=pastas', 'usuario', 'senha');
            $pdo = new PDO('pgsql:host=10.209.9.131;port=5432;dbname=carga_afd;user=cgdms;password=senhacgdms');
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException  $e) {
            echo "Error: " . $e;
        }
        return $pdo;
    }

    /**
     * selecionando o nome das tabelas do banco sei
     * @return array
     */
    public function selectPastas($regime, $start, $end, $upag)
    {
        $result = array();
        try { 
			// Serviço incluirPasta
			$sql = "SELECT nu_orgao_upag, co_orgao_matr matricula_siape, da_ocor_ingr_orgao_serv,
						   nu_cpf, no_servidor, regime_afd, sg_regime_situacao, dt_inclusao_registro
					  FROM tb_siape_servidor_incremental;";
			
			// Serviço incluirServidor
			// $sql = "SELECT nu_orgao_upag AS sigla_unidade, 
						// regime_afd AS tipo_assentamento,
						// no_servidor AS interessado,
						// nu_cpf AS cpf,
						// TO_CHAR(da_ocor_ingr_orgao_serv::timestamp, 'DD/MM/YYYY HH24:MI:SS') AS data_ingresso_cargo,
						// co_orgao_matr AS assentamento
					// FROM tb_siape_servidor_incremental;";
            $consulta = $this->setPDO()->query($sql);
            while ($linha = $consulta->fetch(PDO::FETCH_OBJ)) {
                $result[] = $linha;
            }
			//die('<pre>' . print_r($result, 1));
            return $result;

        } catch (Exception $e) {
            die('<pre>' . print_r($e->getMessage(), 1));
        }
    }
	
    public function selectPastasPorUpag($regime, $upag)
    {
        $result = array();
        try {
			// Serviço incluirPasta
			// Carga da matricula com 12 posições
			$sql = "SELECT LEFT(co_orgao_matr, 5) AS co_orgao, nu_orgao_upag, co_orgao_matr AS matricula_siape, no_servidor, 
						   nu_cpf, regime_afd, da_ocor_ingr_orgao_serv, sg_regime_situacao
					  FROM tb_siape_servidor
					WHERE nu_orgao_upag = '{$upag}'
					ORDER BY nu_orgao_upag, RIGHT(co_orgao_matr, 7), no_servidor";

			// Serviço incluirServidor
			// $sql = "SELECT nu_orgao_upag AS sigla_unidade, 
						// regime_afd AS tipo_assentamento,
						// no_servidor AS interessado,
						// nu_cpf AS cpf,
						// TO_CHAR(da_ocor_ingr_orgao_serv::timestamp, 'DD/MM/YYYY HH24:MI:SS') AS data_ingresso_cargo,
						// co_orgao_matr AS assentamento
					// FROM tb_siape_servidor
					// WHERE nu_orgao_upag = '{$upag}'
					// ORDER BY nu_orgao_upag, co_orgao_matr, no_servidor";			
			
            $consulta = $this->setPDO()->query($sql);
            while ($linha = $consulta->fetch(PDO::FETCH_OBJ)) {
                $result[] = $linha;
            }
            return $result;

        } catch (Exception $e) {
            die('<pre>' . print_r($e->getMessage(), 1));
        }
    }	

    public function selectPastasPorOrgao($regime, $start, $end, $orgao)
    {
        $result = array();
        try {
			// Serviço incluirPasta
			// Carga da matricula com 12 posições
			$sql = "SELECT LEFT(co_orgao_matr, 5) AS co_orgao, nu_orgao_upag, da_ocor_ingr_orgao_serv, da_cadastramento_servidor, 
                    nu_cpf, no_servidor, regime_afd, sg_regime_situacao, tipo, dt_inclusao_registro
					FROM tb_siape_servidor_instituidor
					WHERE LEFT(nu_orgao_upag, 5) = '{$orgao}'
					OFFSET {$start} LIMIT {$end};";
					
			// Serviço incluirServidor
			// $sql = "SELECT sigla_unidade, tipo_assentamento, interessado, cpf, data_ingresso_cargo, 
						   // assentamento, tipo_registro
					// FROM vw_siape_servidor_instituidor
					// WHERE sigla_unidade = '{$orgao}'
					// OFFSET {$start} LIMIT {$end};";	
					
            $consulta = $this->setPDO()->query($sql);
            while ($linha = $consulta->fetch(PDO::FETCH_OBJ)) {
                $result[] = $linha;
            }
			// die('<pre>' . print_r($result, 1));
            return $result;

        } catch (Exception $e) {
            die('<pre>' . print_r($e->getMessage(), 1));
        }
    }		

	// public function selectPastasInstituidores($start, $end, $upag)
	public function selectPastasInstituidores($regime, $upag)
    {
        $result = array();
        try {
			// Serviço incluirPasta
			$sql = "SELECT nu_orgao_upag, co_orgao_matr, da_ocor_ingr_orgao_serv,
						   nu_cpf, no_servidor, regime_afd, sg_regime_situacao, dt_inclusao_registro
					  FROM tb_siape_instituidor
					WHERE nu_orgao_upag = '{$upag}'";
					
			// Serviço incluirServidor
			// $sql = "SELECT nu_orgao_upag AS sigla_unidade, 
						// regime_afd AS tipo_assentamento,
						// no_servidor AS interessado,
						// nu_cpf AS cpf,
						// TO_CHAR(da_ocor_ingr_orgao_serv::timestamp, 'DD/MM/YYYY HH24:MI:SS') AS data_ingresso_cargo,
						// co_orgao_matr AS assentamento
					// FROM tb_siape_instituidor
					// WHERE nu_orgao_upag = '{$upag}'";

            $consulta = $this->setPDO()->query($sql);

            while ($linha = $consulta->fetch(PDO::FETCH_OBJ)) {
                $result[] = $linha;
            }
            // die('<pre>' . print_r($result, 1));
            return $result;

        } catch (Exception $e) {
            die('<pre>' . print_r($e->getMessage(), 1));
        }
    }

	public function selectPastasInstituidoresIncremental()
    {
        $result = array();
        try {
			// Serviço incluirPasta 
			$sql = "SELECT nu_orgao_upag, co_orgao_matr AS matricula_siape, da_ocor_ingr_orgao_serv,
					   nu_cpf, no_servidor, regime_afd, sg_regime_situacao, dt_inclusao_registro
				  FROM tb_siape_instituidor_incremental;";
			
			// Serviço incluirServidor 
			// $sql = "SELECT nu_orgao_upag AS sigla_unidade, 
						// regime_afd AS tipo_assentamento,
						// no_servidor AS interessado,
						// nu_cpf AS cpf,
						// TO_CHAR(da_ocor_ingr_orgao_serv::timestamp, 'DD/MM/YYYY HH24:MI:SS') AS data_ingresso_cargo,
						// co_orgao_matr AS assentamento
				  // FROM tb_siape_instituidor_incremental;";

            $consulta = $this->setPDO()->query($sql);

            while ($linha = $consulta->fetch(PDO::FETCH_OBJ)) {
                $result[] = $linha;
            }
            // die('<pre>' . print_r($result, 1));
            return $result;
        } catch (Exception $e) {
            die('<pre>' . print_r($e->getMessage(), 1));
        }
    }

    public function selectPastasAnistiados($start, $end, $upag)
    {
        $result = array();
        try {

            $sql = "SELECT * FROM anistiados a
                    --and nu_matr_siape = '2308555'
                    ORDER BY a.nome
                    OFFSET($start - 1) LIMIT $end;"; //Postgres

            $consulta = $this->setPDO()->query($sql);

            while ($linha = $consulta->fetch(PDO::FETCH_OBJ)) {
                $result[] = $linha;
            }
            //die('<pre>' . print_r($result, 1));
            return $result;

        } catch (Exception $e) {
            die('<pre>' . print_r($e->getMessage(), 1));
        }
    }

    public function updatePastas($id_servidor)
    {
        // try {
            // $sql = "UPDATE tb_siape_servidor_extr_663834_abr2016 SET carga_afd = true WHERE id_servidor = $id_servidor"; //Postgres

            // $this->setPDO()->query($sql);

        // }catch (Exception $e){
            // die('<pre>'.print_r($e,1));
        // }
    }


    /**
     * m�todo que salva o log no banco
     * @param $table
     * @param $array_data
     */
    public function insertLogPastas($table, $array_data)
    {

        foreach ($array_data as $key => $value) {

            $formate = $this->formateColumns($value);

            //die('<pre>'.print_r($formate,1));

            try {
                $insert = $this->setPdo()->prepare("INSERT INTO $table ( {$formate['columns']} ) VALUES(  {$formate['values']} )");

                $insert->execute($formate['params']);

                //echo 'Erro com a pasta: ' . $value['nu_matricula_siape'] . '. Verifique tabela de log</br>';
                echo 'Pasta [' . $value['co_orgao_matr'] . '] enviada para o SGAFD. Verifique tabela de log para maiores detalhes!</br>';
                // echo 'Pasta [' . $value['assentamento'] . '] enviada para o SGAFD. Verifique a tabela de log para maiores detalhes!</br>';

            } catch (Exception $e) {

                return die(print_r($e->getMessage(), 1));

            }
        }

    }

    public function formateColumns($array_data)
    {
        //definindo array
        $data = array();
        $params = array();
		$data['columns'] = '';
		$data['values'] = '';
		
        //montando os campos, valores e parametros
        foreach ($array_data as $column => $value) {

            $data['columns'] .= $column . ',';
            $data['values'] .= ':' . $column . ',';
            $params[':' . $column] = $value;
            $data['params'] = $params;

        }

        //remomvendo a v�rgula do final de cada campo
        $data['columns'] = rtrim($data['columns'], ',');
        $data['values'] = rtrim($data['values'], ',');

        return $data;
    }

    public function selectLog($data,$upag)
    {
        //die('<pre>'.print_r($upag,1));
        $result = array();
        try {
			$sql = "SELECT
					nu_orgao_upag,
					co_orgao_matr,
					resposta_ws,
					regime_afd_informado, 
					dt_inclusao_reg
					FROM tb_log_incluir_pasta
					WHERE dt_inclusao_reg::date >= '{$data}'
					AND resposta_ws NOT LIKE '%Procedimento%'
					AND nu_orgao_upag LIKE '{$upag}%'
					ORDER BY dt_inclusao_reg DESC;";

            $consulta = $this->setPDO()->query($sql);
            while ($linha = $consulta->fetch(PDO::FETCH_OBJ)) {
                $result[] = $linha;
            }

            return $result;

        } catch (Exception $e) {
            if($e->getCode() == '22007')
                return 'data invalida';
            die('<pre>' . print_r($e->getCode(), 1));
        }

    }



}


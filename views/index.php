<!DOCTYPE html>
<html>
<meta charset="utf-8"/>

<h1><?php echo $title; ?></h1>

<a class="button" href="index.php?app=pasta/deletar">Deletar Pastas</a></br>

<div id="form">
    <form id="iniciar_procedimento" method="post" name="iniciarProcedimento">
        <fieldset>
            <legend>Criar pasta por regime e range de registros</legend>

            <span>
                <label>Carga</label>
                <select name="carga" style="width:150px">
                    <option value="servidores">servidores por upag</option>
                    <option value="servidores-intervalo">servidores incremental</option>
                    <option value="servidores-orgao">servidores por órgão</option>
                    <option value="anistiados">anistiados</option>
                    <option value="instituidores">instituidores por upag</option>
                    <option value="instituidores-incremental">instituidores incremental</option>
                </select>
            </span>

            <span>
            <label>Web Service</label>
            <select name="webservice" style="width:150px">
                <option value="producao">Produção</option>
                <option value="servidor219">Servidor 219</option>
            </select>
			</span>
			
            <span>
                <label>Regime</label>
                <input type="text" name="regime" value="Todos" readonly>
            </span>
            <span>
                <label>Sigla Sistema</label>
                <input type="text" name="sigla_sistema" value="IPF@AFD" readonly>
            </span>
            <span>
                <label>Identificacao Servico</label>
                <input type="text" name="identificacao_servico" value="ServicoIPF@AFD" readonly>
            </span>
            <span>
                <label>Registro inicial</label>
                <!-- input type="text" name="range_inicio" disabled -->
				<!-- Apenas números -->
				<input type="text" name="range_inicio" pattern="[0-9]+$" />
            </span>
            <span>
                <label>Total de registros a enviar</label>
                <!-- input type="text" name="range_fim" disabled -->
				<!-- Apenas números -->
				<input type="text" name="range_fim" pattern="[0-9]+$" />
            </span>
			<span>
                <label>Órgão</label>
				<input type="text" name="orgao" maxlength="5" pattern="[0-9]+$" />
            </span>	
            <span>
                <label>UPAG</label>
				<input type="text" name="upag" maxlength="14" pattern="[0-9]+$" />
            </span>			
            <span>
                <input type="submit" value="Processar" name="iniciarProcedimento"/>
            </span>

        </fieldset>
    </form>
</div>

</body>
</html>


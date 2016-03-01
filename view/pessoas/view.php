<?php
/** @var $pessoa Pessoa * */
use lib\Gamification;
use TiagoGouvea\PLib;

/* @var $inscricao Inscricao */
$inscricoes = $pessoa->getInscricoes();

?>
<div class="wrap">

    <h2><?php echo $pessoa->nome; ?></h2>

    <a href="admin.php?page=Pessoas&action=edit&id=<?php echo $pessoa->id; ?>" class="add-new-h2">Editar</a>
    <a href="admin.php?page=Pessoas&action=add-extra&id=<?php echo $pessoa->id; ?>" class="add-new-h2">Adicionar
        Extra</a>

    <div id="poststuff">

        <div id="postbox-container-2" class="postbox-container">
            <div id="postexcerpt" class="postbox ">
                <h3 class="hndle"><span>Informações e contatos</span></h3>

                <div class="inside">
                    <table width="100%">
                        <tr>
                            <td width="200">
                                <Center>
                                    <img src="<?php echo $pessoa->getPictureUrl(140); ?>"
                                         style="width:140px; margin-right: 8px;">
                                    <br>
                                    <?php echo $pessoa->getExtrasExibicao(null, true, false); ?>
                            </td>
                            <td>
                                <h2><?php echo $pessoa->nome; ?></h2><br>
                                Email: <b><a
                                        href='<?php echo $pessoa->email; ?>'><?php echo $pessoa->email; ?></a></b><br>
                                Celular: <b><?php echo $pessoa->celular; ?></b><br>
                                <?php if ($pessoa->cpf) : ?>
                                    CPF: <b><?php echo $pessoa->cpf; ?></b><br>
                                <?php endif; ?>
                                <?php if ($pessoa->getPassword()) : ?>
                                    Senha Área Restrita: <b><?php echo $pessoa->password; ?></b><br>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (TGO_EVENTO_GAMIFICATION===true): ?>
                                    <b>Índice de Quebra</b> <small> (pós-gamification, de 1/ago/2015 pra cá)</small> <br>
                                    Inscrições Confirmadas: <?php echo count($pessoa->getInscricoesConfirmadas('2015-08-01')); ?><br>
                                    Presenças: <?php echo $pessoa->getCountPresentes('2015-08-01'); ?>
                                    <br>
                                    <?php if ($pessoa->getIndiceQuebra('2015-08-01') !== null): ?>
                                        Índice de Quebra: <?php echo $pessoa->getIndiceQuebra('2015-08-01') . '%'; ?>
                                    <?php endif; ?>
                                <?php else: ?>
                                    Inscrições Confirmadas: <?php echo count($pessoa->getInscricoesConfirmadas()); ?><br>
                                    Presenças: <?php echo $pessoa->getCountPresentes(); ?>
                                    <?php if ($pessoa->getIndiceQuebra() !== null): ?>
                                        Índice de Quebra: <?php echo $pessoa->getIndiceQuebra() . '%'; ?>
                                    <?php endif; ?>
                                <?php endif; ?>



                            </td>
                        </tr>
                        <?php if ($pessoa->hasExtra('observacoes')): ?>
                            <tr>
                                <td colspan="2"><h4>Observações:</h4>
                                    <?php echo $pessoa->getExtra('observacoes'); ?>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </table>
                </div>
            </div>
        </div>

        <?php if ($pessoa->extras != null): ?>
            <div id="postbox-container-2" class="postbox-container">
                <div id="postexcerpt" class="postbox ">
                    <h3 class="hndle"><span>Extras</span></h3>

                    <div class="inside">
                        <table width="100%">
                            <?php
                            // Obter extras para exibição
                            if ($pessoa->extras != null)
                                echo $pessoa->getExtrasExibicao(null, false);
                            ?>
                        </table>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <?php if (TGO_EVENTO_GAMIFICATION === true): ?>
            <div id="postbox-container-2" class="postbox-container">
                <div id="postexcerpt" class="postbox ">
                    <h3 class="hndle"><span>Gamification</span></h3>

                    <div class="inside">
                        <?php
                        $gamification = Gamification::getInstance();
                        $gamification->setUserId($pessoa->id);
                        $gamification->showUserScores();
                        $gamification->showUserLog();
                        $gamification->showUserEvents();
                        ?>
                        <bR>
                        <a href="admin.php?page=Gamification&action=addEventoPessoa&id_pessoa=<?php echo $pessoa->id; ?>"
                           class="add-new-h2">Adicionar Evento a Pessoa</a>
                        <a href="admin.php?page=Gamification&action=addBadgePessoa&id_pessoa=<?php echo $pessoa->id; ?>"
                           class="add-new-h2">Adicionar Badge a Pessoa</a>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <?php if (count($inscricoes) > 0): ?>
            <div id="postbox-container-2" class="postbox-container">
                <div id="postexcerpt" class="postbox ">
                    <h3 class="hndle"><span>Eventos</span></h3>

                    <div class="inside">
                        <table width="100%">
                            <thead>
                            <th>Ticket</th>
                            <th>Evento</th>
                            <th>Inscrição</th>
                            <th>Situação</th>
                            </thead>
                            <?php
                            if (count($inscricoes) > 0) {
                                foreach ($inscricoes as $inscricao):?>
                                    <tr>
                                        <td><?php echo $inscricao->id; ?></td>
                                        <td><?php echo $inscricao->evento()->titulo; ?></td>
                                        <td><?php echo PLib::date_relative($inscricao->data_inscricao); ?></td>
                                        <td><?php echo $inscricao->getSituacaoString() . $inscricao->getSituacaoAcoes(true); ?></td>
                                    </tr>
                                <?php endforeach;
                            }
                            ?>
                        </table>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <div id="postbox-container-2" class="postbox-container">
            <div id="postexcerpt" class="postbox ">
                <h3 class="hndle"><span>Eventos de terceiros</span></h3>

                <div class="inside">
                    <table width="100%" id="inscricoes_terceiros">
                        <thead>
                        <tr>
                            <th>Organizador</th>
                            <th>Data</th>
                            <th>Evento</th>
                            <th>Situação</th>
                        </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
                <script>
                    var inscricoes = [];
                    var updating = false;
                    function updateTable() {
                        if (updating == true) {
                            setTimeout(function () {
                                updateTable();
                            }, 300);
                            return;
                        }
                        updating = true;
//                        console.log('inscricoes', inscricoes);
                        jQuery("#inscricoes_terceiros tbody").html("");

                        inscricoes.map(function (item) {
                            // var myDate="26-02-2012";
                            // 2014-11-24
                            myDate = item.data_evento.split("-");
                            var newDate = myDate[2] + "/" + myDate[1] + "/" + myDate[0]; // m/d/y
                            item.data_evento_timestamp = new Date(newDate).getTime();
                        });

                        inscricoes.sort(function (a, b) {
                            return a.data_evento_timestamp - b.data_evento_timestamp;
                        });

                        console.log('----');

                        inscricoes.map(function (item) {
                            var situacao = null;
                            if (item.confirmado == 1)
                                situacao = "Confirmado<br>";
                            if (item.presente == 1)
                                situacao = situacao + 'Presente<br>';
                            if (item.vencido == 1)
                                situacao = situacao + 'Vencido<br>';
//                            console.log(item.data_evento_timestamp);
                            jQuery('#inscricoes_terceiros tr:last').after(
                                '<tr>' +
                                '<td>' + item.titulo_organizador_evento + '</td>' +
                                '<td>' + item.data_evento + '</td>' +
                                '<td>' + item.titulo_evento + '</td>' +
                                '<td>' + situacao + '</td>' +
                                '</tr>'
                            );
                        });
                        updating = false;
                    }

                    <?php foreach(Organizadores::$organizadoresExternos as $site): ?>
                    var site = '<?php echo $site; ?>';

//                    getAjax('<?php //echo $site.'/api/pessoa/'.md5($pessoa->email).'/inscricoes/'; ?>//',
//                        function (data) {
//                            console.log(data);
//                            if (data == undefined || data.inscricoes == undefined) return;
//                            inscricoes = inscricoes.concat(data.inscricoes);
//                            updateTable();
//                        });
                    <?php endforeach; ?>
                </script>
            </div>
        </div>

        <?php if ($pessoa->id_user): ?>
            <div id="postbox-container-2" class="postbox-container">
                <div id="postexcerpt" class="postbox ">
                    <h3 class="hndle"><span>Administração</span></h3>

                    <div class="inside">
                        Usuário do Wordpress: <?php echo $pessoa->user()->display_name; ?>

                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

</div>

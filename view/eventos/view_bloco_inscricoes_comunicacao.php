<div id="postbox-container-2" class="postbox-container">
    <div id="postexcerpt" class="postbox ">
        <h3 class="hndle"><span>Comunicação</span></h3>

        <div class="inside">
            <table width="100%">
                <thead>
                <th width="300">Ação</th>
                <th>Detalhes</th>
                </thead>
                <!--                        <tr>
                            <Td><a href="admin.php?page=Inscricoes&action=emailPagueAgora&id_evento=<?php echo $evento->id; ?>&nonce='.$nonce.'">Email última chance</a></td>
                            <td>Envia um email para aqueles que não concluiram sua inscrição dando uma última chance</td>
                        </tr>-->
                <tr>
                    <Td>
                        <a href="admin.php?page=Inscricoes&action=exportarInscritos&id_evento=<?php echo $evento->id; ?>&nonce='.$nonce.'">Exportar Emails</a></td>
                    <td>Exportar emails dos inscritos no evento
                    </td>
                </tr>
                <!--                <tr>-->
                <!--                    <Td>-->
                <!--                        <a href="admin.php?page=Inscricoes&action=emailConfirmeAgora&id_evento=--><?php //echo $evento->id; ?><!--&nonce='.$nonce.'">Email-->
                <!--                            "confirme agora"</a></td>-->
                <!--                    <td>Envia um email para aqueles que não concluiram sua inscrição pedindo que cliquem no link-->
                <!--                        e-->
                <!--                        concluam-->
                <!--                    </td>-->
                <!--                </tr>-->
                <tr>
                    <Td>
                        <a href="admin.php?page=Comunicacao&id_evento=<?php echo $evento->id; ?>&action=email&filter=confirmados">
                            Enviar email Confirmados</a></td>
                    <td>Envia um email (a ser escrito) para todos os confirmados</td>
                </tr>
                <tr>
                    <Td>
                        <a href="admin.php?page=Comunicacao&id_evento=<?php echo $evento->id; ?>&action=email&filter=presentes">
                            Enviar email Presentes</a></td>
                    <td>Envia um email (a ser escrito) para todos os Presentes</td>
                </tr>
                <tr>
                    <Td>
                        <a href="admin.php?page=Comunicacao&id_evento=<?php echo $evento->id; ?>&action=email&filter=naoConfirmados">
                            Enviar email para Pendentes</a></td>
                    <td>Envia um email (a ser escrito) para todos os ainda não Confirmados</td>
                </tr>
                <tr>
                    <Td>
                        <a href="admin.php?page=Comunicacao&id_evento=<?php echo $evento->id; ?>&action=email&filter=rejeitados">
                            Enviar email Cancelados/Vencidos</a></td>
                    <td>Envia um email (a ser escrito) para todos os Cancelados e Vencidos</td>
                </tr>
                <tr>
                    <Td>
                        <a href="admin.php?page=Comunicacao&id_evento=<?php echo $evento->id; ?>&action=email&filter=confirmadosFalse">
                            Enviar email <b>não confirmados</b></a></td>
                    <td>Envia um email (a ser escrito) para todos os que não estão confirmados, em qualquer outra situação</td>
                </tr>
                <tr>
                    <Td>
                        <a href="admin.php?page=Comunicacao&id_evento=<?php echo $evento->id; ?>&action=email&filter=preInscritos">
                            Enviar email Pré-Inscritos</a></td>
                    <td>Envia um email (a ser escrito) para todos os Pré-inscritos</td>
                </tr>
                <tr>
                    <Td>
                        <a href="admin.php?page=Comunicacao&id_evento=<?php echo $evento->id; ?>&action=sms&filter=confirmados">
                            Enviar SMS para Confirmados</a></td>
                    <td>Envia um SMS (a ser escrito) para tos os confirmados</td>
                </tr>
<!--                <tr>-->
<!--                    <Td>-->
<!--                        <a href="admin.php?page=Inscricoes&action=sms&subAction=doDia&id_evento=--><?php //echo $evento->id; ?><!--&nonce='.$nonce.'">Enviar SMS-->
<!--                            do dia</a></td>-->
<!--                    <td>Envia o SMS do dia do evento (a ser escrito), após aprovação da mensagem</td>-->
<!--                </tr>-->
            </table>
        </div>
    </div>
</div>
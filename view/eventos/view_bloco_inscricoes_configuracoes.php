<div id="postbox-container-2" class="postbox-container">
    <div id="postexcerpt" class="postbox ">
        <h3 class="hndle"><span>Configurações</span></h3>

        <div class="inside">
            <table width="100%">
                <thead>
                <th width="150">Ação</th>
                <th>Detalhes</th>
                </thead>
                <tr>
                    <Td><a href="post.php?action=edit&post=<?php echo $evento->id; ?>">Editar</a></td>
                    <td>Alterar definições do evento</td>
                </tr>
                <tr>
                    <Td><a href="post.php?page=Eventos&action=editAreaAluno&id=<?php echo $evento->id; ?>">Editar Área do Aluno</a></td>
                    <td>Alterar conteúdo da Área do Aluno</td>
                </tr>
                <tr>
                    <Td><a href="post.php?page=Eventos&action=editCertificado&id=<?php echo $evento->id; ?>">Configurar Certificado</a></td>
                    <td>Inserir e ajustar o certificado</td>
                </tr>
                <tr>
                    <Td><a href="admin.php?page=Categorias&id_evento=<?php echo $evento->id; ?>">Categorias</a>
                    </td>
                    <td>Gerenciar categorias de inscrição</td>
                </tr>
                <?php if ($evento->pago=="pago"): ?>
                <tr>
                    <Td><a href="admin.php?page=Precos&id_evento=<?php echo $evento->id; ?>">Preços</a>
                    </td>
                    <td>Gerenciar preços e lotes do evento</td>
                </tr>
                <tr>
                    <Td><a href="admin.php?page=Descontos&id_evento=<?php echo $evento->id; ?>">Tickets de
                            Desconto</a></td>
                    <td>Gerenciar tickets de desconto</td>
                </tr>
                <?php endif; ?>
            </table>
        </div>
    </div>
</div>
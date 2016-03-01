<?php
use TiagoGouvea\PLib;

$template = get_query_var('inscricao');

get_header();

// Determinar etapa
$etapa = $_POST['etapa'];
if ($etapa == null) {
    $etapa = 1;
}
$avancarTexto = "Avançar";

// Tipos de template
$exibirTitulo = false;
$exibirResumo = false;
$exibirResumo = $template == 3;
$exibirTitulo = $template == 3;

// Ter evento em mãos
$evento = get_the_evento();
//var_dump($evento);

// Postando
if (count($_POST) > 0) {
    // Sanitizar e validar entrada de dados
    $dados = false;
    $erro = null;
    $avancar = false;
    // Etapa 1
    if ($etapa == 1) {
        $result = Inscricoes::getInstance()->aplicarTicket($evento,$_POST['ticket']);
        if ($result!==true){
            $erro = $result;
        } else {
            $etapa = $etapa + 1;
        }
    }
    // Etapa 2
    if ($etapa == 2) {
        $nome = sanitize_text_field($_POST['nome']);
        if (!$nome)
            $erro = "Informe seu nome";
        $dados = $nome != null;
    }
}

if ($etapa == 1) $etapaTitulo = 'Aplicar Desconto';
if ($etapa == 2) $etapaTitulo = 'Aplicar Desconto';
?>

    <div class="main-content">
        <div class="row">
            <div class="widget">
                <div class="widget-title">
                    <h3 class="nomargin"><?php echo $etapaTitulo; ?></h3></div>
                <div class="widget-content">
                    <form method="post" class="formatted-form">
                        <input type="hidden" name="etapa" value="<?php echo $etapa; ?>">

                        <?php if ($etapa == 1): ?>
                            <?php if ($erro): ?>
                                <p><b><?php echo $erro; ?></b></p>
                            <?php endif; ?>

                            <p>Informe no campo abaixo seu ticket de desconto para que o valor do treinamento seja
                                atualizado.</p>
                            <div class="field-wrapper">
                                <?php echo input_texto_simples('ticket', 'Ticket de desconto', 20); ?>
                            </div>

                        <?php endif; ?>

                        <?php if ($etapa == 2): ?>
                            <?php if (hasFlash()): ?>
                                <?php echo getFlashString(); ?>
                            <?php endif; ?>
                            <br><br>
                            <a href="<?php echo the_permalink() . "?inscricao=1"; ?>"
                               class="button medium price-button submit-button left">Realização inscrição agora!</a>
                            <br><br><br><br>
                            <A href="<?php the_permalink(); ?>">Voltar para a página do treinamento.</a>
                        <?php endif; ?>

                        <?php if ($etapa < 2): ?>
                            <br>
                            <input type="submit" value="<?php echo $avancarTexto; ?>">
                        <?php endif; ?>
                    </form>
                </div>
            </div>
        </div>
    </div>

<?php
get_footer();



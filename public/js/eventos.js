

function inscrever(chave){
    // Verificar CPF
    chave = chave.trim();
    var valorChave = document.getElementById("input_"+chave).value;
    valorChave = valorChave.trim();
    console.log(valorChave);
    if (valorChave==""){
        alert("Digite algo!");
        return;
    }
    if (chave=="cpf"){
        if (!vercpf(valorChave)){
            alert('O CPF informado é inválido.');
            return;
        } else {
            valorChave = valorChave.replace(/[^z0-9]/gi,'');
        }
    } else if (chave=="email"){
        if (!validateEmail(valorChave)){
            alert('Email inválido.');
            return;
        } else {
            valorChave = valorChave.toLowerCase();
        }
    }

    url = "?inscricao=1&"+chave+"="+valorChave;
    window.location = url;
}

function vercpf (cpf){
    var cpf = cpf.replace(/[^z0-9]/gi,'');
    if (cpf.length != 11 || cpf == "00000000000" || cpf == "11111111111" || cpf == "22222222222" || cpf == "33333333333" || cpf == "44444444444" || cpf == "55555555555" || cpf == "66666666666" || cpf == "77777777777" || cpf == "88888888888" || cpf == "99999999999")
        return false;
    add = 0;
    for (i=0; i < 9; i ++)
        add += parseInt(cpf.charAt(i)) * (10 - i);
    rev = 11 - (add % 11);
    if (rev == 10 || rev == 11)
        rev = 0;
    if (rev != parseInt(cpf.charAt(9)))
        return false;
    add = 0;
    for (i = 0; i < 10; i ++)
        add += parseInt(cpf.charAt(i)) * (11 - i);
    rev = 11 - (add % 11);
    if (rev == 10 || rev == 11)
        rev = 0;
    if (rev != parseInt(cpf.charAt(10)))
        return false;
    return true;
}

function validateEmail(email) {
    var re = /^([\w-]+(?:\.[\w-]+)*)@((?:[\w-]+\.)*\w[\w-]{0,66})\.([a-z]{2,6}(?:\.[a-z]{2})?)$/i;
    return re.test(email);
}

// Método genérico para requisições
function getAjax(url, callback) {
    // Omitir div de alerta
    jQuery.get(url, function (data) {
        var erro = null;
        if (!data) {
            console.log('Erro em '+url);
            //console.log('null?',data);
            erro = "Ops.. algo deu errado. :(";
//                        console.log(url);
//                        console.log(data);
        }
        //if(data.Falha!=undefined)erro=data.Falha;
        //if(data.erro) erro=data.erro;
        // Algum erro?
        if (erro != null) {
            //alert(erro);
            return false;
        }
        // Sucesso! Chamar callback
        callback(data);
    })
        .done(function () {
            //alert("second success");
        })
        .fail(function () {
            //showError(url);
            alert("Ocorreu um erro na requisição");
        });
}
$(function() {
    let $ville = $('#sortie_ville')
    $ville.on('change', function () {
        let $form = $(this).closest('form')
        let data = {}

        data['sortie[dateHeureDebut]'] = new Date()
        data['sortie[dateLimiteInscription]'] = new Date()
        data[$ville.attr('name')] = $ville.val()
        $.ajax({
            url : $form.attr('action'),
            type: $form.attr('method'),
            data : data,
            complete: function(html) {
                // Replace current lieu field ...
                $('#sortie_lieu').replaceWith(
                    // ... with the returned one from the AJAX response.
                    $(html.responseText).find('#sortie_lieu')
                );
                // Lieu field now displays the appropriate lieu.

                $('#sortie_rue').replaceWith(
                    $(html.responseText).find('#sortie_rue')
                );
                $('#sortie_codePostal').replaceWith(
                    $(html.responseText).find('#sortie_codePostal')
                );
                $('#sortie_latitude').replaceWith(
                    $(html.responseText).find('#sortie_latitude')
                );
                $('#sortie_longitude').replaceWith(
                    $(html.responseText).find('#sortie_longitude')
                );
            }
        });
    })

    let $lieu = $('#sortie_lieu')
    $lieu.on('change', function () {
        let $form = $(this).closest('form')
        let data = {}

        data['sortie[dateHeureDebut]'] = new Date()
        data['sortie[dateLimiteInscription]'] = new Date()
        data[$lieu.attr('name')] = $lieu.val()
        console.log(data)
        $.ajax({
            url : $form.attr('action'),
            type: $form.attr('method'),
            data : data,
            complete: function(html) {// Replace current lieu field ...
                $('#sortie_lieu').replaceWith(
                    // ... with the returned one from the AJAX response.
                    $(html.responseText).find('#sortie_lieu')
                );
                // Lieu field now displays the appropriate lieu.

                $('#sortie_rue').replaceWith(
                    $(html.responseText).find('#sortie_rue')
                );
                $('#sortie_codePostal').replaceWith(
                    $(html.responseText).find('#sortie_codePostal')
                );
                $('#sortie_latitude').replaceWith(
                    $(html.responseText).find('#sortie_latitude')
                );
                $('#sortie_longitude').replaceWith(
                    $(html.responseText).find('#sortie_longitude')
                );
            }
        });
    })

    let formSortie = document.getElementById('idForm');
    let sortieButtons = formSortie.getElementsByTagName('button')
    for (let i = 0; i < sortieButtons.length; i++) {
        sortieButtons[i].classList.add('btn-lg')
        if (i==2) {
            sortieButtons[i].classList.add('btn-success')
        }
        if (i==3) {
            sortieButtons[i].classList.add('btn-danger')
        }
    }
})

function ajaxFunctions(p1) {

}
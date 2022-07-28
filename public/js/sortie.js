$(function() {
    let $ville = $('#sortie_ville')
    $ville.on('change', function () {
        let $form = $(this).closest('form')
        let data = {}

        data['sortie[dateHeureDebut]'] = new Date()
        data['sortie[dateLimiteInscription]'] = new Date()
        data[$ville.attr('name')] = $ville.val()
        console.log($ville.attr('name'))
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
                // Position field now displays the appropriate lieu.

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
})
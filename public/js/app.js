$( document ).ready(function() {
    rechercherVille('');

    $('#inputFiltrerVille').on('keyup', function(){
        rechercherVille( $('#inputFiltrerVille').val());
    });

    function rechercherVille(term){
        $.ajax({
            method: "POST",
            url: "/sortir/public/ville/recherche",
            data: { 'recherche' : term },
            success: function(reponse){
                console.log(reponse)
                var head = $('<tr><th>Nom</th><th>Code postal</th><th>Actions</th></tr>');
                $('#table_ville').html(' ');
                $('#table_ville').append(head);

                for(var i = 0 ; i < reponse.length ; i++){
                    var ville = reponse[i];
                    var nouvelleLigne = $(
                        '<tr id="row'+ville["id"]+'">' +
                        '<td id="row_nom_ville'+ville["id"]+'"></td>' +
                        '<td id="row_code_postal_ville'+ville["id"]+'"></td>' +
                        '<td id="boutons"></td>' +
                        '</tr>');
                    $('#row_nom_ville'+ville["id"], nouvelleLigne).html(ville["nom"]);
                    $('#row_code_postal_ville'+ville["id"], nouvelleLigne).html(ville["code_postal"]);
                    $('#boutons', nouvelleLigne)
                        .append($('<input />')
                            .attr({
                                'type': 'button',
                                'value' : 'Modifier',
                                'class' : 'btn btn-primary m-2',
                                'id' : 'mod_but'+ville["id"]
                            })
                            .on('click', { id : ville["id"]}, mod_row)
                        )
                        .append($('<input />')
                            .attr({
                                'type': 'button',
                                'value' : 'Sauvegarder',
                                'class' : 'btn btn-primary d-none m-2',
                                'id' : 'save_but'+ville["id"]
                            })
                            .on('click', {id : ville["id"]} , save_row)
                        )
                        .append($('<input />')
                            .attr({
                                'type': 'button',
                                'value' : 'Supprimer',
                                'class' : 'btn btn-primary m-2',
                                'id' : 'suppr_but'+ville["id"]
                            })
                            .on('click', {id : ville["id"]} , suppr_row)
                        )
                    $('#table_ville').append(nouvelleLigne);
                }
                var ligneAjout = $(
                    '<tr>\n' +
                    '<td><input type="text" class="form-control" id="new_nom_ville"></td>' +
                    '<td><input type="text" class="form-control" id="new_code_postal_ville"></td>' +
                    '<td><input type="button" id="ajout_but" value="Ajouter" class="btn btn-primary" /></td>' +
                    '</tr>'
                )
                $('#ajout_but', ligneAjout).on('click', function () {
                    var new_nom = $('#new_nom_ville').val();
                    var new_cp_ville = $('#new_code_postal_ville').val();
                    $.ajax({
                        method: "POST",
                        url: "/sortir/public/ville/ajouter",
                        data: {'nom_ville': new_nom, 'cp_ville': new_cp_ville}
                    }).done(function (response) {
                        $('#message').html(
                            '<div class="alert alert-success" role="alert">' +
                            response +
                            '</div>'
                        )
                        rechercherVille( $('#inputFiltrerVille').val());
                    }).fail(function (xhr) {
                        console.log(xhr)
                        $('#message').html(
                            '<div class="alert alert-danger" role="alert">' +
                                xhr.responseJSON.message +
                            '</div>'
                        )
                    })
                })
                $('#table_ville').append(ligneAjout);
            }
        })
    }

    function mod_row(event)
    {
        var no = event.data.id;
        $('#mod_but'+no).toggleClass('d-none');
        $('#save_but'+no).toggleClass('d-none');
        var nom_ville = $('#row_nom_ville'+no).text()
        var code_postal = $('#row_code_postal_ville'+no).text()
        $('#row_nom_ville'+ no)
        .html(' ')
        .append($('<input />')
            .attr({
                'id' : 'input_nom_ville' + no,
                'value' : nom_ville
            })
        )
        $('#row_code_postal_ville'+ no)
        .html(' ')
        .append($('<input />')
            .attr({
                'id' : 'input_code_postal_ville' + no,
                'value' : code_postal
            })
        )
    }

    function save_row(event)
    {
        var no = event.data.id;
        var nom_ville = $('#input_nom_ville'+no).val();
        var code_postal_ville = $('#input_code_postal_ville'+no).val();

        $.ajax({
            method: "POST",
            url: "/sortir/public/ville/modifier",
            data: {
                'id': no ,
                'nom_ville' : nom_ville,
                'code_postal_ville' : code_postal_ville
            }
        }).done(function () {
            $('#message').html(
                '<div class="alert alert-success" role="alert">' +
                'Ville modifiée avec succes.' +
                '</div>'
            )
            rechercherVille( $('#inputFiltrerVille').val());
        }).fail(function () {
            $('#message').html(
                '<div class="alert alert-danger" role="alert">' +
                'Un problème est survenu lors de la modification.' +
                '</div>'
            )
        })

        $('#row_nom_ville'+ no)
            .html(nom_ville)
        $('#row_code_postal_ville'+ no)
            .html(code_postal_ville)
        $('#mod_but'+no).toggleClass('d-none');
        $('#save_but'+no).toggleClass('d-none');
    }

    function suppr_row(event) {
        var no = event.data.id;
        $.ajax({
            method: "POST",
            url: "/sortir/public/ville/supprimer",
            data: {
                'id': no
            }
        }).done(function () {$('#message').html(
                '<div class="alert alert-success" role="alert">' +
                'Ville supprimée avec succes.' +
                '</div>'
            )
            rechercherVille( $('#inputFiltrerVille').val());
        }).fail(function () {
            $('#message').html(
                '<div class="alert alert-danger" role="alert">' +
                'Un problème est survenu lors de la suppression de la ville [Ville liée à des lieux].' +
                '</div>'
            )
        })
    }

    $('#btnRechercherAjax').on('click', function () {
       rechercheSortieDetaillee();
    })

    function rechercheSortieDetaillee(){
        console.log($('#recherche_terme').val());
        console.log($('#recherche_site').val());
        console.log($('#recherche_etat').val());
        console.log($('#date_debut').val());
        console.log($('#date_fin').val());
        console.log($('#cb_organisateur').is(':checked'));
        console.log($('#cb_inscrit').is(':checked'));
        console.log($('#cb_non_inscrit').is(':checked'));
        console.log($('#cb_passee').is(':checked'));

        $.ajax({
            method: "GET",
            url: "/sortir/public/sortie/listeajax",
            data: {
                'user_id' :  $('#user_id').val(),
                'recherche_terme': ($.trim($('#recherche_terme').val()) != '' ? $('#recherche_terme').val() : null),
                'recherche_site' : $('#recherche_site').val(),
                'recherche_etat': $('#recherche_etat').val(),
                'date_debut': $('#date_debut').val(),
                'date_fin': $('#date_fin').val(),
                'cb_organisateur': $('#cb_organisateur').is(':checked'),
                'cb_inscrit': $('#cb_inscrit').is(':checked'),
                'cb_non_inscrit': $('#cb_non_inscrit').is(':checked'),
                'cb_passee': $('#cb_passee').is(':checked')
            }
        }).done(function (response) {
            $('#table_des_sorties').html(' ');
            $('#table_des_sorties').html(response);
            console.log(response)
        })
    }
});

$(window).on('load', function () {
    $('#modal_alert').modal('show');
})

$(document).on('change', '#sortie_ville', function () {
    chargerListeLieux();
})

function chargerListeLieux(){
    $.ajax({
        method: "POST",
        url: "/sortir/public/lieu/rechercheAjaxByVille",
        data: {
            'ville_id' : $('#sortie_ville').val()
        }
    }).done(function (response) {
        $('#sortie_lieu').html('');
        for(var i = 0 ; i < response.length ; i++) {
            var lieu = response[i];
            let option = $('<option value="'+lieu["id"]+'">'+lieu["nom"]+'</option>');
            $('#sortie_lieu').append(option);
        }
    })
}
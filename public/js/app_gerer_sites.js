$(document).ready(function() {
    rechercherSite('');
    $('#inputFiltrerSite').on('keyup', function(){
        rechercher( $('#inputFiltrerSite').val());
    });

    function rechercherSite(term){
        $.ajax({
            method: "POST",
            url: "/sortir/public/site/recherche",
            data: { 'recherche' : term },
            success: function(reponse){
                console.log(reponse)
                var head = $('<tr><th>Nom</th><<th>Actions</th></tr>');
                $('#table_site').html(' ');
                $('#table_site').append(head);

                for(var i = 0 ; i < reponse.length ; i++){
                    var site = reponse[i];
                    var nouvelleLigne = $(
                        '<tr id="row'+site["id"]+'">' +
                        '<td id="row_nom_site'+site["id"]+'"></td>' +
                        '<td id="boutons"></td>' +
                        '</tr>');
                    $('#row_nom_site'+site["id"], nouvelleLigne).html(site["nom"]);
                    $('#boutons', nouvelleLigne)
                        .append($('<input />')
                            .attr({
                                'type': 'button',
                                'value' : 'Modifier',
                                'class' : 'btn btn-primary m-2',
                                'id' : 'mod_but'+site["id"]
                            })
                            .on('click', { id : site["id"]}, mod_row)
                        )
                        .append($('<input />')
                            .attr({
                                'type': 'button',
                                'value' : 'Sauvegarder',
                                'class' : 'btn btn-primary d-none m-2',
                                'id' : 'save_but'+site["id"]
                            })
                            .on('click', {id : site["id"]} , save_row)
                        )
                        .append($('<input />')
                            .attr({
                                'type': 'button',
                                'value' : 'Supprimer',
                                'class' : 'btn btn-primary m-2',
                                'id' : 'suppr_but'+site["id"]
                            })
                            .on('click', {id : site["id"]} , suppr_row)
                        )
                    $('#table_site').append(nouvelleLigne);
                }
                var ligneAjout = $(
                    '<tr>\n' +
                    '<td><input type="text" class="form-control" id="new_nom_site"></td>' +
                    '<td><input type="button" id="ajout_but" value="Ajouter" class="btn btn-primary" /></td>' +
                    '</tr>'
                )
                $('#ajout_but', ligneAjout).on('click', function () {
                    var new_nom = $('#new_nom_site').val();
                    $.ajax({
                        method: "POST",
                        url: "/sortir/public/site/ajouter",
                        data: {'nom_site': new_nom}
                    }).done(function (response) {
                        $('#message').html(
                            '<div class="alert alert-success" role="alert">' +
                            response +
                            '</div>'
                        )
                        rechercherSite( $('#inputFiltrersite').val());
                    }).fail(function (xhr) {
                        console.log(xhr)
                        $('#message').html(
                            '<div class="alert alert-danger" role="alert">' +
                                xhr.responseJSON.message +
                            '</div>'
                        )
                    })
                })
                $('#table_site').append(ligneAjout);
            }
        })
    }

    function mod_row(event)
    {
        var no = event.data.id;
        $('#mod_but'+no).toggleClass('d-none');
        $('#save_but'+no).toggleClass('d-none');
        var nom_site = $('#row_nom_site'+no).text()
        $('#row_nom_site'+ no)
        .html(' ')
        .append($('<input />')
            .attr({
                'id' : 'input_nom_site' + no,
                'value' : nom_site
            })
        )
    }

    function save_row(event)
    {
        var no = event.data.id;
        var nom_site = $('#input_nom_site'+no).val();

        $.ajax({
            method: "POST",
            url: "/sortir/public/site/modifier",
            data: {
                'id': no ,
                'nom_site' : nom_site
            }
        }).done(function () {
            $('#message').html(
                '<div class="alert alert-success" role="alert">' +
                'Site modifié avec succes.' +
                '</div>'
            )
            rechercherSite( $('#inputFiltrersite').val());
        }).fail(function () {
            $('#message').html(
                '<div class="alert alert-danger" role="alert">' +
                'Un problème est survenu lors de la modification.' +
                '</div>'
            )
        })

        $('#row_nom_site'+ no)
            .html(nom_site)
        $('#mod_but'+no).toggleClass('d-none');
        $('#save_but'+no).toggleClass('d-none');
    }

    function suppr_row(event) {
        var no = event.data.id;
        $.ajax({
            method: "POST",
            url: "/sortir/public/site/supprimer",
            data: {
                'id': no
            }
        }).done(function () {
            $('#message').html(
                '<div class="alert alert-success" role="alert">' +
                'Site supprimé avec succes.' +
                '</div>'
            )
            rechercherSite( $('#inputFiltrersite').val());
        }).fail(function () {
            $('#message').html(
                '<div class="alert alert-danger" role="alert">' +
                'Un problème est survenu lors de la suppression du site [Site lié à des sorties].' +
                '</div>'
            )
        })
    }
});
/*
Utilisé pour la selection de date (datetimepicker)
By Thierry Largeau
*/
$(document).ready(function(){
    $temp_date =  $('#sortie_dateHeureDebut').html();
    $('#sortie_dateHeureDebut').addClass('d-none');
    $('#sortie_dateHeureDebut').after($('<input type="text" id="datetime_temp" class="form-control" placeholder="Selectionner une date de début de la sortie" required/>'));
    $('#datetime_temp').datetimepicker({
        minuteStep: 5,
        todayBtn: true,
        autoclose: true,
        format: 'dd/mm/yyyy hh:ii',
        weekStart : 1,
        fontAwesome: true
    })
        .on('changeDate', function (ev) {
            console.log($('#datetime_temp').val())
            $jour = $('#datetime_temp').val().substring(0,2);
            if($jour.substring(0,1) == '0'){
                $jour = $jour.replace('0','');
            }
            $mois = $('#datetime_temp').val().substring(3,5);
            if($mois.substring(0,1) == '0'){
                $mois = $mois.replace('0','');
            }
            $annee = $('#datetime_temp').val().substring(6,10);
            $heure =  $('#datetime_temp').val().substring(11,13);
            if($heure.substring(0,1) == '0'){
                $heure = $heure.replace('0','');
            }
            $minute =  $('#datetime_temp').val().substring(14,16);
            if($minute.substring(0,1) == '0'){
                $minute = $minute.replace('0','');
            }

            $('#sortie_dateHeureDebut_date_year').val($annee);
            $('#sortie_dateHeureDebut_date_month').val($mois);
            $('#sortie_dateHeureDebut_date_day').val($jour);
            $('#sortie_dateHeureDebut_time_hour').val($heure);
            $('#sortie_dateHeureDebut_time_minute').val($minute);
        })
    ;

    $temp_date =  $('#sortie_dateLimiteInscription').html();
    $('#sortie_dateLimiteInscription').addClass('d-none');
    $('#sortie_dateLimiteInscription').after($('<input type="text" id="datetime_temp2" class="form-control" placeholder="Selectionner une date de fin d\'inscription" required/>'));
    $('#datetime_temp2').datetimepicker({
        minuteStep: 5,
        todayBtn: true,
        autoclose: true,
        format: 'dd/mm/yyyy hh:ii',
        weekStart : 1,
        fontAwesome: true
    })
        .on('changeDate', function (ev) {
            console.log($('#datetime_temp2').val())
            $jour = $('#datetime_temp2').val().substring(0,2);
            if($jour.substring(0,1) == '0'){
                $jour = $jour.replace('0','');
            }
            $mois = $('#datetime_temp2').val().substring(3,5);
            if($mois.substring(0,1) == '0'){
                $mois = $mois.replace('0','');
            }
            $annee = $('#datetime_temp2').val().substring(6,10);
            $heure =  $('#datetime_temp2').val().substring(11,13);
            if($heure.substring(0,1) == '0'){
                $heure = $heure.replace('0','');
            }
            $minute =  $('#datetime_temp2').val().substring(14,16);
            if($minute.substring(0,1) == '0'){
                $minute = $minute.replace('0','');
            }

            $('#sortie_dateLimiteInscription_date_year').val($annee);
            $('#sortie_dateLimiteInscription_date_month').val($mois);
            $('#sortie_dateLimiteInscription_date_day').val($jour);
            $('#sortie_dateLimiteInscription_time_hour').val($heure);
            $('#sortie_dateLimiteInscription_time_minute').val($minute);
        })
    ;

    $temp_date =  $('#debut').html();
    $('#debut').addClass('d-none');
    $('#debut').after($('<input type="text" id="datetime_temp3" class="form-control" placeholder="Selectionner une date de début" required/>'));
    $('#datetime_temp3').datetimepicker({
        minuteStep: 5,
        todayBtn: true,
        autoclose: true,
        format: 'dd/mm/yyyy hh:ii',
        weekStart : 1,
        fontAwesome: true
    })
        .on('changeDate', function (ev) {
            console.log($('#datetime_temp3').val())
            $jour = $('#datetime_temp3').val().substring(0,2);
            if($jour.substring(0,1) == '0'){
                $jour = $jour.replace('0','');
            }
            $mois = $('#datetime_temp3').val().substring(3,5);
            if($mois.substring(0,1) == '0'){
                $mois = $mois.replace('0','');
            }
            $annee = $('#datetime_temp3').val().substring(6,10);
            $heure =  $('#datetime_temp3').val().substring(11,13);
            if($heure.substring(0,1) == '0'){
                $heure = $heure.replace('0','');
            }
            $minute =  $('#datetime_temp3').val().substring(14,16);
            if($minute.substring(0,1) == '0'){
                $minute = $minute.replace('0','');
            }

            $('#debut_date_year').val($annee);
            $('#debut_date_month').val($mois);
            $('#debut_date_day').val($jour);
            $('#debut_time_hour').val($heure);
            $('#debut_time_minute').val($minute);
        })
    ;

})

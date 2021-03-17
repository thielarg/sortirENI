function change(){
    $temp_date =  $('#sortie_dateHeureDebut').html();
    $('#sortie_dateHeureDebut').addClass('d-none');
    $('#sortie_dateHeureDebut').after($('<input type="text" id="datetime_temp" class="form-control" placeholder="Selectionner une date de début" required/>'));
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
}
$( document ).ready(function() {
    //console.log( "ready!" );
    // change the color of private areas of players
    var arrRedArea = ['0208', '0308', '0408', '0508', '0608', '0708'];
    var arrBlueArea = ['0809', '0810', '0811', '0812', '0813', '0814'];
    var arrYellowArea = ['0908', '1008', '1108', '1208', '1308', '1408'];
    var arrGreenArea = ['0802', '0803', '0804', '0805', '0806', '0807'];

    jQuery.each(arrRedArea, function(index, item) {
        $("#box_" + item).addClass("box-red");
    });

    jQuery.each(arrBlueArea, function(index, item) {
        $("#box_" + item).addClass("box-blue");
    });

    jQuery.each(arrYellowArea, function(index, item) {
        $("#box_" + item).addClass("box-yellow");
    });

    jQuery.each(arrGreenArea, function(index, item) {
        $("#box_" + item).addClass("box-green");
    });

    // make all clickable area disabled
    $("#all-stick-positions").addClass("disabledbutton");
    // refresh the whole game
    $("#refresh").click(function () {
        if(confirm("Are you sure to end this game?")) {
            $("#all-stick-positions").addClass("disabledbutton");
            $("#dice").attr("disabled",false);

            $.ajax({ url: "/ludo/ludoProcess.php",
                data: {data: JSON.stringify({action: 'refresh'})},
                type: 'post',
                success: function(result){
                    location.reload();
                }});
        }
    });

    // roll the dice
    $("#dice").click(function(){

        $.ajax({ url: "/ludo/ludoProcess.php",
                data: {data: JSON.stringify({action: 'diceRoll'})},
                type: 'post',
                success: function(result){
                    $("#all-stick-positions").removeClass("disabledbutton");
                    $("#dice-result").html(result);
                    //$("#dice").attr("disabled",true);
                }});
    });

    // requesting to move a specific piece after roll the dice
    $(".stick_position").click(function(event){
        //$("#dice").attr("disabled",true);
        var dataString = {action: 'move', boxId: event.target.id, currentValue: $(this).html()}; //console.log($('#' + event.target.id).html());
        var existingValue = $(this).html();

        $.ajax({ url: "/ludo/ludoProcess.php",
            data: {data: JSON.stringify(dataString)},
            type: 'post',
            success: function(result){
                $("#all-stick-positions").addClass("disabledbutton");
                //$("#dice").attr("disabled",false);

                // if wrong move or request
                if(result.search("Wrong") >= 0 || result.search("end") >= 0 || result.search("try") >= 0 || result.search("move") >= 0){
                    alert(result);
                    $("#all-stick-positions").removeClass("disabledbutton");

                }else{
                    // changing the place for a piece from current box to new box
                    $('#' + event.target.id).html(result);
                    var arr = event.target.id.split('_');
                    var move = arr[1] + arr[2];

                    if(existingValue.length > 0){
                        var existingBoxValue = $('#box_' + existingValue).html();
                        if(existingBoxValue.length > 0){ //alert(existingBoxValue); alert(move.toUpperCase());
                            var existingBoxNewValue = existingBoxValue.replace(move.toUpperCase(), '');
                        }
                        /*var lastChar = existingBoxNewValue[existingBoxNewValue.length -1];
                        if(lastChar == ','){
                            existingBoxNewValue.slice(0,-1);
                        }*/
                        $('#box_' + existingValue).html(existingBoxNewValue);
                    }

                    var updatedValue = $('#box_' + result).html();
                    if(updatedValue.length > 0){
                        updatedValue += ' ';
                    }

                    if(existingValue == result){
                        $("#all-stick-positions").removeClass("disabledbutton");
                    }

                    $('#box_' + result).html(updatedValue + move.toUpperCase());

                }
            }});
    });
});

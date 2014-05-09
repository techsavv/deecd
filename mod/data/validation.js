
    $(document).ready(function(){

        $('#menuf_2').change(function(){
            $('tr.validate_notice').remove();
        });
        $('#menuf_3').change(function(){
            $('tr.validate_notice').remove();
        });
        $('#menuf_4').change(function(){
            $('tr.validate_notice').remove();
        });
        
    });

    function validation(){
        var mp_issue = $('#menuf_2').val();
        var concern_lv = $('#menuf_3').val();
        var young_age = $('#menuf_4').val();
        if(mp_issue == "" || mp_issue == null){
            $( 'table.mod-data-default-template tbody tr.r0:first-child' ).before( "<tr class=\"validate_notice\"><td></td><td><p style=\"color: red;\">*Please select Main presenting issue.</p></td></tr>" );
            return false;
        }else if(concern_lv == "" || concern_lv == null){
            $( 'table.mod-data-default-template tbody tr.r0:first-child' ).before( "<tr class=\"validate_notice\"><td></td><td><p style=\"color: red;\">*Please select Level of concern.</p></td></tr>" );
            return false;
        }else if(young_age == "" || young_age == null){
            $( 'table.mod-data-default-template tbody tr.r0:first-child' ).before( "<tr class=\"validate_notice\"><td></td><td><p style=\"color: red;\">*Please select Age of child or young person.</p></td></tr>" );
            return false;
        }
    }
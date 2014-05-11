
    $(document).ready(function(){
        $( 'table.mod-data-default-template tbody tr td').find('select').change(function(){
            $('.boxaligncenter tr.validate_notice').remove();
        });        
    });

    function validation(){
        $('.boxaligncenter tr.validate_notice').remove();
        var flag = 0;
        $( 'table.mod-data-default-template tbody tr td').find('select').each(function(){
            var mandatory_field = $(this).val();
            if(mandatory_field == "" || mandatory_field == null){
                flag = 1;
            }
        });
        if(flag == 1){
            $( 'table.mod-data-default-template tbody tr:first-child' ).before( "<tr class=\"validate_notice\"><td><p style=\"color: #DD2F28;\">Please select all fields</p></td></tr>" );
            return false;
        }
    }
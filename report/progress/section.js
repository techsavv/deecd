$(function(){ 
    $('#completion-progress').on('click','.toggle',function(){ 
        if($(this).hasClass('open')){ 
            var i = 1; 
            while($('.Mod'+i).length){ 
                if($(this).hasClass('Mod'+i)) 
                { 
                    $('.module'+i).css('display','none'); 
                    $(this).attr('rowspan','999'); 
                    $(this).attr('colspan','1'); 
                    $(this).removeClass('open'); 
                    $(this).addClass('closed'); 
                    break; 
                } 
                i++; 
            } 
        }else if($(this).hasClass('closed')){ 
            var i = 1; 
            while($('.Mod'+i).length){ 
                if($(this).hasClass('Mod'+i)) 
                { 
                    $('.module'+i).css('display','table-cell'); 
                    $(this).attr('rowspan','1'); 
                    $(this).attr('colspan',$('th.module'+i).length); 
                    $(this).removeClass('closed'); 
                    $(this).addClass('open'); 
                    break; 
                } 
                i++; 
            } 
        } 
          
    }); 
});

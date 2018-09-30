/**
* 大蒙<修改> 
* 2018-1-22 10:37
*/

;(function($,window, undefined) {
    'use strict';

    /* Check jquery */
    if(typeof($) === 'undefined') throw new Error('muu requires jQuery');

    // muu shared object
    if(!$.muu) $.muu = function(obj) {
        if($.isPlainObject(obj)) {
            $.extend($.muu, obj);
        }
    };

    var target_change = function(){
        $('.target').change(function(){
            $(this).closest('.new-blank').find('.target_input').val($(this).is(':checked')?1:0);
        })
    };

    var change_select = function () {
        $('.nav-type').unbind('change')
        $('.nav-type').change(function () {
            var obj = $(this);
            switch (obj.val()) {
                case 'module':
                    obj.closest('li>div').children('select.module').show().change();
                    obj.closest('li>div').children('input.url').hide();
                    break;
                case 'custom':
                    obj.closest('li>div').children('select.module').hide();
                    obj.closest('li>div').children('input.url').show();
                    obj.closest('li>div').children('input.title').val('');
                    obj.closest('li>div').children('input.url').val('');
                    break;
            }
        })
    };

    var change_module = function () {
        $('.module').unbind('change')
        $('.module').change(function () {
            var obj = $(this);
            var text = obj.find("option:selected").text();
            var value = obj.val();
            obj.closest('li>div').children('input.title').val(text);
            obj.closest('li>div').children('input.url').val(value);

            obj.closest('li>div').next().children('select.chosen-icons').attr('data-value','icon-'+obj.find("option:selected").data('icon'));
            re_bind()
        })

    };

    var add_new = function () {
        $('.add-new').unbind('click');
        $('.add-new').click(function () {
            $('.channel-ul').append($('#one-nav').html());
            re_bind()
        })
    };

    var add_one = function () {
        $('.add-one').unbind('click');
        $('.add-one').click(function () {
            $(this).closest('.pLi').after($('#one-nav').html());
            re_bind()
        })
    };

    var add_two = function () {
        $('.add-two').unbind('click');
        $('.add-two').click(function () {
            $(this).closest('li').after($('#two-nav').html());
            re_bind()
        })
    };

    var remove_li = function () {
        $('.remove-li').unbind('click');
        $('.remove-li').click(function () {
            if( $(this).parents('form').find('.pLi').length > 1){
                $(this).closest('li').remove()
                re_bind()
            }else{
                updateAlert('不能再减了~');
            }

        })
    };

    var add_child = function () {
        $('.add-child').unbind('click');
        $('.add-child').click(function () {
            if ($(this).closest('li').find('.ul-2').length == 0) {
                $(this).closest('li').append('<div class="clearfix"></div><ul class="ul-2"  style="display: block;"></ul>')
            }
            $(this).closest('li').find('.ul-2').prepend($('#two-nav').html());
            re_bind()
        })
    };

    var add_flag = function () {
        $('.channel-ul .pLi').each(function (index, element) {
            $(this).attr('data-id', index);
            $(this).find('.sort').val($(this).attr('data-order'));
        })
        $('.ul-2 li').each(function (index, element) {
            $(this).find('.pid').val($(this).parents('.pLi').attr('data-id'));
            $(this).find('.sort').val($(this).attr('data-order'));
        })
    };

    //执行
    var re_bind = function(){
        change_select();
        change_module();
        add_new();
        add_one();
        add_two();
        remove_li();
        add_child();
        add_flag();
        target_change();
    };

    re_bind();
    
    var fix_form = function () {
        $('.channel-ul').sortable({trigger: '.sort-handle-1', selector: 'li', dragCssClass: '',finish:function(){
            re_bind()
        }
        });
        $('.channel-ul .ul-2').sortable({trigger: '.sort-handle-2', selector: 'li', dragCssClass: '',finish:function(){
            re_bind()
        }});

    };

    var bind_color = function () {
        $('.simpleColorContainer').remove()
        $('.simple_color_callback').simpleColor({
            boxWidth: 15,
            boxHeight: 15,
            cellWidth: 15,
            cellHeight: 15,
            chooserCSS: { 'z-index': 1200 },
            displayCSS: { 'border': 0 }
        });
    };

    $.muu({
        channel_sort: fix_form,
        channel_bind_color:bind_color,
    });

}(jQuery, window, undefined));

<div class="body">
<!-- -->
<link rel="stylesheet" type="text/css" media="screen" href="<?=$base_url?>node_modules/free-jqgrid/dist/css/ui.jqgrid.css" />

<script type="text/javascript" src="<?=$base_url?>node_modules/free-jqgrid/dist/i18n/grid.locale-ru.js"></script>
<script type="text/javascript" src="<?=$base_url?>node_modules/free-jqgrid/dist/jquery.jqgrid.min.js"></script>
<script type="text/javascript" src="<?=$base_url?>node_modules/free-jqgrid/dist/modules/min/grid.subgrid.js"></script>
<!-- -->

<table id="table_users"></table>
<div id="additional_panel"></div>

<script type="text/javascript">

jQuery("#table_users").jqGrid({ // Привязка плагина к таблице
   	url: '<?=$base_url?>admins/users/get_list_users', // Скрипт - обработчик ваших запросов
   	editurl: '<?=$base_url?>admins/users/panel_users',
   	datatype: "json", // Формат скрипта-обработчика
   	colNames: ['Номер', 'Логин', 'Тип', 'Email', 'Пароль', 'Имя', 'Отчество', 'Фамилия', 'Статус аккаунта','Дата регистрации'],
			colModel: [
				{name:"id", index:"u.id", width:80, searchtype:"integer", align:'center', sortable:true},
				{name:"login", index:"u.login", width:80, searchtype:"string", align:'center', sortable:true, editable:true, editrules:{required:true}},
                {name:"type", index:"u.type", width:80, searchtype:"string", align:'center', sortable:true, editable:true, edittype:'select', editoptions:{value:{'user':'Пользователь', 'admin':'Администратор'}}, editrules:{required:true}},
                {name:"email", index:"u.email",width:80, sortable:true, searchtype:"string", align:'center', editable:true, editrules:{email:true, required:true}},
                {name:"password", index:"u.password", width:80, sortable:false, searchtype:"string", align:'center', editable:true, edittype:'password', editrules:{required:true}},
				{name:"firstname", index:"u.firstname", width:80, sortable:true, searchtype:"string", align:'center', editable:true, editrules:{required:true}},
				{name:"middlename", index:"u.middlename", width:80, sortable:true, searchtype:"string", align:'center', editable:true, editrules:{required:true}},
				{name:"lastname", index:"u.lastname", width:80, sortable:true, searchtype:"string", align:'center', editable:true, editrules:{required:true}},
				{name:"activity", index:"u.activity", width:80, sortable:true, searchtype:"integer", align:'center', editable:true, edittype:"checkbox", editoptions: {value:"1:0", defaultValue:"1"}, formatter:'checkbox'},
                {name:"date", index:"u.date", width:120, sortable:true, searchtype:"string", align:'center'},
			],
            
   	subGrid : true, 
   	subGridRowExpanded: function(subgrid_id, row_id) {
		var subgrid_table_id, pager_id;
		subgrid_table_id = subgrid_id+"_t"; 
		pager_id = "p_"+subgrid_table_id;
		$("#"+subgrid_id).html("<table id='"+subgrid_table_id+"' class='scroll'></table><div id='"+pager_id+"' class='scroll'></div>");
		jQuery("#"+subgrid_table_id).jqGrid({
			url: '<?=$base_url?>admins/users/get_user_sites?id='+row_id,
			editurl: '<?=$base_url?>admins/users/panel_users_site?id='+row_id+'&id_user='+row_id,
			datatype: "json",
            colNames:['Номер', 'Сайт', 'Статус', 'Дата установки'],
   	        colModel:[
                {name:"id", index:'r.id_site', width:10, searchtype:"integer", align:'center'},
                {name:"site", index:'s.site', align:'center', width:120, searchtype:"string", edittype:'select', editable:true,  editoptions:{dataUrl:'<?=$base_url?>admins/users/get_sites?id_user='+row_id}},
                {name:"status", index:'r.status', align:'center', width:20, searchtype:"string", editable:true, edittype:"checkbox", editoptions: {value:"1:0", defaultValue:'1'}, formatter:'checkbox'},
                {name:"date", index:"r.date", width:80, sortable:true, searchtype:"string", align:'center'},
   	        ],
			rowNum:20,
			pager: pager_id,
			sortname: 's.site',
			sortorder: 'desc',
			height: '100%',
			width: 1000
		});
		jQuery("#"+subgrid_table_id).jqGrid('navGrid',"#"+pager_id,{edit:true,add:true,del:true},
            {afterSubmit: function(response) {
                        var json = response.responseText;
                        try  {
                            var text = window.JSON.parse(json);
                            if  (typeof(text) != 'undefined')  {
                                return [false, text.message];
                            }  else  {
                                return [true, ''];
                            }
                        }  catch(e)  {
                            return [true, ''];
                        }
	               },
            afterShowForm: function(formid)  {
                            $(formid).find('#tr_site').css('display','none');
                            },
                            
            },
            {afterSubmit: function(response) {
                                var json = response.responseText;
                                try  {
                                    var text = window.JSON.parse(json);
                                    if  (typeof(text) != 'undefined')  {
                                        return [false, text.message];
                                    }  else  {
                                        return [true, ''];
                                    }
                                }  catch(e)  {
                                    return [true, ''];
                                }
                        },
             afterShowForm: function(formid)  {
                                $(formid).find('#tr_site').css('display','table-row');
                            },
            },
            {afterSubmit: function(response) {
                        var json = response.responseText;
                        try  {
                            var text = window.JSON.parse(json);
                            if  (typeof(text) != 'undefined')  {
                                return [false, text.message];
                            }  else  {
                                return [true, ''];
                            }
                        }  catch(e)  {
                            return [true, ''];
                        }
	               }}
        )
	},

	caption: "Пользователи",
   	rowNum:10,
   	rowList:[10,20,30],
   	width:1200,
   	height: '100%',
   	pager: '#additional_panel', // Привязка к таблице тулбара
   	sortname: 'u.type',
   	viewrecords: true,
   	sortorder: "desc"
});

jQuery("#table_users").jqGrid('navGrid','#additional_panel',  // Управление тулбаром таблицы
    {edit:true,add:true,del:true}, // Отключаем от тулбара редактирование, добавление и удаление записей. На тулбаре останутся только две кнопки: "Поиск" и "Обновить"
    {afterSubmit: function(response) {
                        var json = response.responseText;
                        try  {
                            var text = window.JSON.parse(json);
                            if  (typeof(text) != 'undefined')  {
                                return [false, text.message];
                            }  else  {
                                return [true, ''];
                            }
                        }  catch(e)  {
                            return [true, ''];
                        }
	               }
    }, // Опции окон редактирования
    {afterSubmit: function(response) {
                        var json = response.responseText;
                        try  {
                            var text = window.JSON.parse(json);
                            if  (typeof(text) != 'undefined')  {
                                return [false, text.message];
                            }  else  {
                                return [true, ''];
                            }
                        }  catch(e)  {
                            return [true, ''];
                        }
	               }
    },
    {afterSubmit: function(response) {
                        var json = response.responseText;
                        try  {
                            var text = window.JSON.parse(json);
                            if  (typeof(text) != 'undefined')  {
                                return [false, text.message];
                            }  else  {
                                return [true, ''];
                            }
                        }  catch(e)  {
                            return [true, ''];
                        }
	               }
    }
);
	
</script>

</div>
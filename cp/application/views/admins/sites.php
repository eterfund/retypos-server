<div class="body">
<!-- -->
<link rel="stylesheet" type="text/css" media="screen" href="<?=$base_url?>javascript/jquery_plugins/jqGrid/4.4.0/css/ui.jqgrid.css" />

<script type="text/javascript" src="<?=$base_url?>javascript/jquery_plugins/jqGrid/4.4.0/js/i18n/grid.locale-ru.js"></script>
<script type="text/javascript" src="<?=$base_url?>javascript/jquery_plugins/jqGrid/4.4.0/js/jquery.jqGrid.min.js"></script>
<script type="text/javascript" src="<?=$base_url?>javascript/jquery_plugins/jqGrid/4.4.0/src/grid.subgrid.js"></script>
<!-- -->

<table id="table_sites"></table>
<div id="additional_panel"></div>

<script type="text/javascript">

jQuery("#table_sites").jqGrid({ // Привязка плагина к таблице
   	url: '<?=$base_url?>admins/sites/get_list_sites', // Скрипт - обработчик ваших запросов
   	editurl: '<?=$base_url?>admins/sites/panel_sites',
   	datatype: "json", // Формат скрипта-обработчика
   	colNames:['Номер', 'Сайт', 'Дата регистрации'],
   	colModel:[
   		{name:"id", index:'s.id', width:10, searchtype:"integer", align:'center'},
        {name:"site", index:'s.site', align:'center', width:20, searchtype:"string", editable:true, addtable:true, editrules:{required:true}},
        {name:"date", index:'s.date', align:'center', width:20, searchtype:"string"},
   	],

   	subGrid : true, 
   	subGridRowExpanded: function(subgrid_id, row_id) {
		var subgrid_table_id, pager_id;
		subgrid_table_id = subgrid_id+"_t"; 
		pager_id = "p_"+subgrid_table_id;
		$("#"+subgrid_id).html("<table id='"+subgrid_table_id+"' class='scroll'></table><div id='"+pager_id+"' class='scroll'></div>");
		jQuery("#"+subgrid_table_id).jqGrid({
			url: '<?=$base_url?>admins/sites/get_list_users?id='+row_id,
			editurl: '<?=$base_url?>admins/sites/panel_users?id='+row_id+'&id_site='+row_id,
			datatype: "json",
			colNames: ['Номер', 'Логин', 'Тип', 'Email', 'Имя', 'Отчество', 'Фамилия', 'Статус аккаунта', 'Подписка', 'Дата установки'],
			colModel: [
				{name:"id", index:"u.id", width:80, searchtype:"integer", align:'center', sortable:true},
				{name:"login", index:"u.login", width:80, searchtype:"string", align:'center', sortable:true},
                {name:"type", index:"u.type", width:80, searchtype:"string", align:'center', sortable:true},
                {name:"email", index:"u.email",width:80, sortable:true, searchtype:"string", align:'center'},
				{name:"firstname", index:"u.firstname", width:80, sortable:true, searchtype:"string", align:'center'},
				{name:"middlename", index:"u.middlename", width:80, sortable:true, searchtype:"string", align:'center'},
				{name:"lastname", index:"u.lastname", width:80, sortable:true, searchtype:"string", align:'center'},
				{name:"activity", index:"u.activity", width:80, sortable:true, searchtype:"integer", align:'center'},
				{name:"status", index:"r.status", width:80, sortable:true, searchtype:"integer", align:'center'},
                {name:"rdate", index:"r.date", width:80, sortable:true, searchtype:"string", align:'center'}
			],
			rowNum:20,
			pager: pager_id,
			sortname: 'u.date',
			sortorder: 'desc',
			height: '100%',
			width: 1000
		});
		jQuery("#"+subgrid_table_id).jqGrid('navGrid',"#"+pager_id,{edit:false,add:false,del:true})
	},

	caption: "Сайты",
   	rowNum:10,
   	rowList:[10,20,30],
   	width:1200,
   	height: '100%',
   	pager: '#additional_panel', // Привязка к таблице тулбара
   	sortname: 's.id',
   	viewrecords: true,
   	sortorder: "asc"
});

jQuery("#table_sites").jqGrid('navGrid','#additional_panel',  // Управление тулбаром таблицы
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
	               }}, // Опции окон редактирования
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
	               }},
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
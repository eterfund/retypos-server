<div class="body">
    <!-- -->
    <link rel="stylesheet" type="text/css" media="screen" href="<?= $base_url ?>javascript/jquery_plugins/jqGrid/4.4.0/css/ui.jqgrid.css" />

    <script type="text/javascript" src="<?= $base_url ?>javascript/jquery_plugins/jqGrid/4.4.0/js/i18n/grid.locale-ru.js"></script>
    <script type="text/javascript" src="<?= $base_url ?>javascript/jquery_plugins/jqGrid/4.4.0/js/jquery.jqGrid.min.js"></script>
    <script type="text/javascript" src="<?= $base_url ?>javascript/jquery_plugins/jqGrid/4.4.0/src/grid.subgrid.js"></script>
    <script type="text/javascript" src="<?= $base_url ?>javascript/typos.js"></script>

    <!-- -->


    <table id="table_sites"></table>
    <div id="additional_panel"></div>


    <script type="text/javascript">

        jQuery("#table_sites").jqGrid({//Привязка плагина к таблице
            url: '<?= $base_url ?>users/typos/get_list_sites', //Получить список сайтов пользователя
            editurl: '<?= $base_url ?>users/typos/panel_sites',
            datatype: "json", //Формат скрипта-обработчика
            colNames: ['Номер', 'Сайт', 'Подписан'],
            colModel: [
                {name: "id", index: 's.id', width: 10, searchtype: "integer", align: 'center'},
                {name: "site", index: 's.site', align: 'center', width: 20, searchtype: "string"},
                {name: "status", index: 'r.status', editable: true, align: 'center', width: 20, edittype: "checkbox", editoptions: {value: "1:0"}, searchtype: "integer", formatter: 'checkbox'}
            ],
            //Подтаблица с сообщениями об опечатках
            subGrid: true,
            subGridRowExpanded: function (subgrid_id, row_id) {
                //Дополнительные переменные
                var subgrid_table_id, pager_id;
                subgrid_table_id = subgrid_id + "_t";
                pager_id = "p_" + subgrid_table_id;
                $("#" + subgrid_id).html("<table id='" + subgrid_table_id + "' class='scroll'></table><div id='" + pager_id + "' class='scroll'></div>");
                jQuery("#" + subgrid_table_id).jqGrid({
                    url: '<?= $base_url ?>users/typos/get_list_messages?id=' + row_id,
                    editurl: '<?= $base_url ?>users/typos/panel_messages?id_site=' + row_id + '&id=' + row_id,
                    datatype: "json",
                    colNames: ['Номер', 'Принято?', 'Ссылка', 'Текст', 'Исправление', 'Контекст', 'Дата добавления'],
                    colModel: [
                        {name: "id", index: "m.id", width: 80, searchtype: "integer", align: 'center'},
                        {name: "status", index: "m.status", width: 80, editable: true, edittype: "checkbox", editoptions: {value: "1:0"}, searchtype: "integer", align: 'center', formatter: 'checkbox'},
                        {name: "link", index: "m.link", width: 80, sortable: false, searchtype: "string", align: 'center', editable: true},
                        {name: "error_text", index: "m.error_text", width: 80, sortable: false, searchtype: "string", align: 'center', editable: true},
                        {name: "comment", index: "m.comment", width: 80, sortable: false, searchtype: "string", align: 'center', editable: true},
                        {name: "context", index: "m.context", width: 80, searchtype: "text", sortable: false, editable: false, align: 'center'},
                        {name: "date", index: "m.date", width: 120, searchtype: "date", align: 'center'},
                    ],
                    rowNum: 20,
                    pager: pager_id,
                    sortname: 'm.date',
                    sortorder: 'desc',
                    height: '100%',
                    width: 1000,
                });

                jQuery("#" + subgrid_table_id).jqGrid(
                        'navGrid', "#" + pager_id,
                        {edit: true, add: true, del: true},
                        {
                            //Скрываем ненужные поля при редактировании
                            afterShowForm: function () {
                                $('#tr_link').css('display', 'none');
                                $('#tr_error_text').css('display', 'none');
                                $('#tr_comment').css('display', 'none');
                            },
                        },
                        {
                            //При добавлении показываем поля (т.к. редактирование - скрывает их навсегда)
                            afterShowForm: function () {
                                $('#tr_link').css('display', 'table-row');
                                $('#tr_error_text').css('display', 'table-row');
                                $('#tr_comment').css('display', 'table-row');
                            }
                        }
                )
            },
            caption: "Сайты",
            rowNum: 10,
            rowList: [10, 20, 30],
            width: 1200,
            height: '100%',
            pager: '#additional_panel', //Привязка к таблице тулбара
            sortname: 's.id',
            viewrecords: true,
            sortorder: "asc"
        });

        jQuery("#table_sites").jqGrid(
                'navGrid', '#additional_panel', //Управление тулбаром таблицы
                {edit: true, add: false, del: false} //Отключаем от тулбара редактирование, добавление и удаление записей. На тулбаре останутся только две кнопки: "Поиск" и "Обновить"
        );

    </script>

    <div id="context-block" title="Контекст ошибки" class="hidden">
        <p id="context-block-text"></p>
    </div>
</div>
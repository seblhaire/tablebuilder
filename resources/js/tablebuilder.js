import $ from 'jquery';
import * as luxon from "luxon";
/**
 Extendable base class for table cells
 */
const NONE = 2;
const ASC = 0;
const DESC = 1;

var TableBuilderBaseCell = {
    type: '', //column  type
    table: null, //link to table
    options: null, //parameters
    mapToData: null, // data field where to retrieve column content
    cellIndex: null, // cell index
    cell: null, // td tag
    line: null, // tr line obj
    arrowspan: null, // arrows span in column headers
    ascImg: null, // em tag for ascending order
    descImg: null, // em tag for descending order
    sortStatus: NONE, //ASC  / DESC
    //sub_constructor common to all cell types
    /*
     @param  TableBuilder table
     @param string mapToData data field
     @param object options
     */
    _init: function (table, mapToData, options) {
        this.table = table;
        this.options = options;
        this.mapToData = mapToData;
        if (this.options == undefined) {
            throw 'options not defined';
        }
        this.setOrderOptions();

    },
    //set column order
    attachOrder: function (index) {
        this.cellIndex = index;
    },
    // build header cell
    buildhead: function () {
        let newth = $('<th></th>');
        newth.attr('id', this.table.tableid + '_head_' + this.cellIndex).attr('scope', 'col');
        if (this.options.width != undefined) {
            newth.attr('width', this.options.width);
        }
        if (this.options.completetitle != undefined) {
            let newa = $('<a></a>').text('\xa0'); //&nbsp;
            newa.attr('href', '#');
            newa.attr('title', this.options.completetitle);
            newa.on('click', function (e) {
                e.preventDefault();
            });
            newth.append(newa);
            newa.html(this.options.title);
        } else {
            let title = '';
            if (this.options.sortable == true) {
                title = this.options.title + "&nbsp;";
            } else {
                title = this.options.title;
            }
            newth.html(title);
        }
        if (this.options.sortable == true) {
            newth.addClass('sortable');
            this.ascImg = $('<em/>');
            this.ascImg.attr('id', this.table.tableid + '_head_' + this.cellIndex + '_asc');
            this.ascImg.addClass(this.table.options.uparrow);
            if (this.sortStatus != ASC) {
                this.ascImg.attr('style', 'display:none');
            }
            this.descImg = $('<em/>');
            this.descImg.attr('id', this.table.tableid + '_head_' + this.cellIndex + '_desc');
            this.descImg.addClass(this.table.options.downarrow);
            if (this.sortStatus != DESC) {
                this.descImg.attr('style', 'display:none');
            }
            this.arrowspan = $('<span></span>');
            this.arrowspan.addClass('arrows');
            this.arrowspan.append(this.ascImg);
            this.arrowspan.append(this.descImg);
            newth.append(this.arrowspan);
            newth.on('mouseover', {self: this}, this.onHeaderOver);
            newth.on('mouseout', {self: this}, this.onHeaderOut);
            newth.on('click', {self: this}, this.onHeaderClick);
        }
        return newth;
    },
    // display arrows function
    displayarrows: function () {
        if (!this.table.modecard && this.arrowspan != null){
            this.arrowspan.show();
            if (this.sortStatus != DESC) {
                this.ascImg.show();
                this.descImg.hide();
            } else {
                this.ascImg.hide();
                this.descImg.show();
            }
        }
    },
    hidearrows: function () {
        if (!this.table.modecard && this.arrowspan != null){
            this.arrowspan.hide();
        }
    },
    // on mouse over
    onHeaderOver: function (event) {
        var self = event.data.self;
        self.displayarrows();
    },
    // on mouse out
    onHeaderOut: function (event) {
        var self = event.data.self;
        if (self.sortStatus == NONE) {
            self.hidearrows();
        }
    },
    // reset sort status
    resetOrder: function () {
        if (this.options.sortable == true) {
            this.sortStatus = NONE;
            this.hidearrows();
        }
    },
    // return order parameter to table
    setOrderQuery: function () {
        return this.table.getColumnOrder(this.mapToData, this.sortStatus);
    },
    // set column sort statsu
    setOrderOptions: function () {
        if (this.options.defaultOrder == 'asc') {
            this.sortStatus = ASC;
        } else if (this.options.defaultOrder == 'desc') {
            this.sortStatus = DESC;
        } else {
            this.sortStatus = NONE;
        }
    },
    // action on column header click
    onHeaderClick: function (event) {
        var self = event.data.self;
        event.preventDefault();
        var oldstatus = self.sortStatus;
        self.table.resetOrder(false);
        self.table.resettablepage();
        if (oldstatus !=  ASC) {
            self.sortStatus = ASC;
        } else {
            self.sortStatus = DESC;
        }
        self.table.tableParams.sortBy = self.setOrderQuery();
        self.displayarrows();
        self.table.setUrl();
        self.table.reload();
    },
    buildContainer: function(lineIndex) {
        if (this.table.modecard){
            this._buildCardContent(lineIndex);
        } else {
            this._buildCell(lineIndex);
        }
    },
    // common cell building functions
    _buildCell: function (lineIndex) {
        this.cell = $('<td></td>');
        this.cell.attr('id', this.table.tableid + '_dataline_' + lineIndex + '_' + this.cellIndex);
        if (this.options.classes != undefined) {
            this.cell.addClass(this.options.classes);
        }
    },
    _buildCardContent: function (lineIndex) {
        this.cell = $('<div></div>');
        this.cell.attr('id', this.table.tableid + '_dataline_' + lineIndex + '_' + this.cellIndex);
        if (this.options.classes != undefined) {
            this.cell.addClass(this.options.cardcontentclass);
        }
        var title = '';
        if (this.options.completetitle != undefined) {
            title = this.options.completetitle + '&nbsp;:&nbsp;';
        } else {
            if (this.options.title != undefined && this.options.title != '') {
                title = this.options.title + '&nbsp;:&nbsp;';
            }
        }
        this.cell.append(title);
    },
    builddata: function (lineobj, lineIndex, content) {
        this.line = lineobj;
        this.buildContainer(lineIndex);
        this.cell.append(this.printData(content, lineIndex));
        return this.cell;
    }
};

/*
 Simple data cell
 */
var TableBuilderDataCell = {
    // constructor
    /*
     @param  TableBuilder table
     @param string mapToData data field
     @param object options
     */
    init: function (table, mapToData, options) {
        this.type = 'DataCell';
        this._init(table, mapToData, options);
    },
    // build cell
    printData: function(content, lineIndex){
        return content[this.mapToData];
    }
};
$.extend(TableBuilderDataCell, TableBuilderBaseCell);

// Action(s) cell
var TableBuilderActionCell = {
    // constructor
    /*
     @param  TableBuilder table
     @param string mapToData data field
     @param object options
     */
    init: function (table, mapToData, options) {
        this.type = 'ActionCell';
        this._init(table, mapToData, options);
    },
    // trigger action
    onActionClick: function (event) {
        var self = event.data.self;
        var data = event.data.data;
        var js = event.data.action.js;
        event.preventDefault();
        if (js instanceof Function) {
            js(data);
        } else {
            js = window[js];
            js(data);
        }
    },
    // build action buttons
    printData: function (content, lineIndex) {
        let newImg = $('<em></em>').addClass(this.options.icoclass).html('&nbsp;');
        let btn = $('<button></button>').addClass('dropdown').attr('data-bs-toggle',"dropdown");
        if (this.options.actiontext.length > 0){
            btn.attr('title', this.options.actiontext);
        } 
        btn.append(newImg);
        let ul = $('<ul></ul>').addClass('dropdown-menu');
        let actionList = null;
        if (content[this.mapToData] instanceof Array) {
            actionList = content[this.mapToData];
        } else if (this.options.actions instanceof Array) {
            actionList = this.options.actions; 
        }
        for (var i = 0; i < actionList.length; i++) {
            let newa = $('<a></a>').addClass('dropdown-item').attr('href', '#');
            let ico = null;
            if (actionList[i].placeholder != undefined) {
                ico = $('<em></em>').attr('class', 'glyphicon').html('&nbsp;');
            } else if (actionList[i].em != undefined) {
                ico = $('<em></em>').attr('class', actionList[i].em);
            }
            newa.append(ico);
            if (actionList[i].text != undefined) {
                newa.append(' ' + actionList[i].text);
            }
            newa.on('click', {self: this, action: actionList[i], data: content}, this.onActionClick);
            let li = $('<li></li>').append(newa);
            ul.append(li);
        }
        btn.append(ul);
        return btn;
    },
    // replaces default head builder
    buildhead: function () {
        let newth = $('<th></th>');
        newth.attr('id', this.table.tableid + '_head_' + this.cellIndex);
        if (this.options.width != undefined) {
            newth.attr('width', this.options.width);
        }

        if (this.options.completetitle != undefined) {
            let newa = $('<a></a>').text('\xa0'); //&nbsp;
            newa.attr('href', '#');
            newa.attr('title', this.options.completetitle);
            newa.on('click', function (e) {
                e.preventDefault();
            });
            newth.append(newa);
            newa.html(this.options.title);
        } else {
            title = this.options.title;
            newth.html(title);
        }
        return newth;
    }
};
$.extend(TableBuilderActionCell, TableBuilderBaseCell);

// checkbox cell
var TableBuilderCheckboxCell = {
    // constructor
    /*
     @param  TableBuilder table
     @param string mapToData data field
     @param object options
     */
    init: function (table, mapToData, options) {
        this.type = 'CheckboxCell';
        this._init(table, mapToData, options);
    },
    printData: function(content, lineIndex){
        let newChk = $('<input/>').attr('id', this.table.tableid + '_chkbox_' + lineIndex + '_' + this.cellIndex);
        newChk.attr('type', 'checkbox');
        if (typeof content[this.mapToData] === "boolean") {
            newChk.attr('checked', content[this.mapToData]);
        } else if (content[this.mapToData] == 1) {
            newChk.attr('checked', true);
        } else if (content[this.mapToData] == 0) {
            newChk.attr('checked', false);
        } else {
            throw 'bad type';
        }
        if (this.options.isEnabledCallback != null) {
            if (!this.options.isEnabledCallback(this, content)) {
                newChk.attr('disabled', 'yes');
            }
        }
        newChk.on('click', {elt: newChk, action: this.options.action, content: content, index: lineIndex}, this.onInputClick);
        return newChk;
    },
    // action on input click
    onInputClick: function (event) {
        var action = event.data.action;
        if (action != null) {
            action(event);
        }
    }
};
$.extend(TableBuilderCheckboxCell, TableBuilderBaseCell);

// date cell
var TableBuilderDateCell = {
    // constructor
    /*
     @param  TableBuilder table
     @param string mapToData data field
     @param object options
     */
    init: function (table, mapToData, options) {
        this.type = 'DateCell';
        this._init(table, mapToData, options);
    },
    printData: function(content, lineIndex){
        let datestr = '';
        if (content[this.mapToData] != undefined) {
            let date = content[this.mapToData];
            if (!isNaN(parseFloat(date)) && isFinite(date)){
                datestr = DateTime.fromSeconds(date).toFormat(this.options.format);
            } else{
                if (date.length == 10) {
                    date += ' 00:00:00';
                }
                datestr = DateTime.fromFormat(date, 'yyyy-MM-dd HH:mm:ss').toFormat(this.options.format);
            } 
        }
        return datestr;
    }
};
$.extend(TableBuilderDateCell, TableBuilderBaseCell);

// image cell
var TableBuilderImageCell = {
    // constructor
    /*
     @param  TableBuilder table
     @param string mapToData data field
     @param object options
     */
    init: function (table, mapToData, options) {
        this.type = 'ImageCell';
        this._init(table, mapToData, options);
    },
    printData : function(content, lineIndex){
        let newImg = '';
        if (content[this.mapToData] != undefined) {
            newImg = $('<' + this.options.tag + '/>');
            if (this.options.tag == 'img') {
                newImg.attr('src', content[this.mapToData])
                if (this.options.imgclass != undefined){
                    newImg.addClass(this.options.imgclass);
                }
            } else {
                newImg.addClass(content[this.mapToData]);
            }
        }
        return newImg;
    }
};
$.extend(TableBuilderImageCell, TableBuilderBaseCell);

// link cell
var TableBuilderLinkCell = {
    // constructor
    /*
     @param  TableBuilder table
     @param string mapToData data field
     @param object options
     */
    init: function (table, mapToData, options) {
        this.type = 'LinkCell';
        this._init(table, mapToData, options);
    },
    printData: function(content, lineIndex){
        let newa = null;
        if (content[this.mapToData] != undefined) {
            let text = content[this.mapToData];
            if (this.options.shorten) {
                if (text.startsWith('https://')) {
                    text = text.substring(8);
                }
                if (text.startsWith('http://')) {
                    text = text.substring(7);
                }
                if (text.length > this.options.maxlength) {
                    text = text.substring(0, this.options.maxlength) + ' ...';
                }
            }
            //'maxlength
            newa = $('<a></a>');
            newa.attr('href', content[this.mapToData]);
            newa.attr('target', this.options.target);
            newa.attr("rel", "noopener noreferrer");
            newa.attr('title', content[this.mapToData]);
            newa.html(text);
        }
        return newa;
    }
};
$.extend(TableBuilderLinkCell, TableBuilderBaseCell);

// mail cell
var TableBuilderMailCell = {
    // constructor
    /*
     @param  TableBuilder table
     @param string mapToData data field
     @param object options
     */
    init: function (table, mapToData, options) {
        this.type = 'MailCell';
        this._init(table, mapToData, options);
    },
    printData: function(content, lineIndex){
        let newa = null;
        if (content[this.mapToData] != undefined) {
            newa = $('<a></a>');
            if (this.options.copycell == true) {
                newa.attr('href', '#').attr('title', this.options.copytext).on('click', this.onclick);
            } else {
                newa.attr('href', 'mailto:' + content[this.mapToData]);
            }
            newa.html(content[this.mapToData]);
        }
        return newa;
    },
    // click mail cell
    onclick: function (event) {
        event.preventDefault();
        if (navigator.clipboard != undefined){
            navigator.clipboard.writeText($(event.target).text());
        }
    }
};
$.extend(TableBuilderMailCell, TableBuilderBaseCell);

// numeric cell
var TableBuilderNumericCell = {
    // constructor
    /*
     @param  TableBuilder table
     @param string mapToData data field
     @param object options
     */
    init: function (table, mapToData, options) {
        this.type = 'NumericCell';
        this._init(table, mapToData, options);
    },
    // number formatter
    format: function (number) {
        let iNumber = parseFloat(number);
        if (isNaN(iNumber)) {
            return number;
        } else {
            iNumber = iNumber.toFixed(this.options.decimals);
            let formatted = String(iNumber);
            let i = formatted.indexOf('.');
            if (this.options.decimalsep != '.') {
                formatted = formatted.replace('.', this.options.decimalsep);
            }
            if (iNumber > 999) {
                i -= 3;
                while (i > 0) {
                    formatted = formatted.substring(0, i) + this.options.thousandsep + formatted.substring(i);
                    i -= 3;
                }
            }
            return formatted;
        }
    },
    printData: function(content, lineIndex){
        if (content[this.mapToData] != undefined) {
            let number = this.format(content[this.mapToData]);
            if (this.options.currency.length > 0) {
                let formatted = '';
                if (this.options.currencyposafter) {
                    formatted += number + ' ' + this.options.currency;
                    
                } else {
                    formatted += '<span class="currency">' + this.options.currency + '</span>';
                    formatted += '<span>' + number + '</span>';
                }
                return formatted;
            } else {
                return number;
            }
        }
        return '';
    }
};
$.extend(TableBuilderNumericCell, TableBuilderBaseCell);

// status cell
var TableBuilderStatusCell = {
    // constructor
    /*
     @param  TableBuilder table
     @param string mapToData data field
     @param object options
     */
    init: function (table, mapToData, options) {
        this.type = 'StatusCell';
        this._init(table, mapToData, options);
    },
    printData: function(content, lineIndex){
        let newImg = null;
        if (content[this.mapToData] != undefined) {
            newImg = $('<em/>');
            newImg.attr('style', this.options.aIcons[content[this.mapToData]].style);
            newImg.attr('title', this.options.aIcons[content[this.mapToData]].title);
            newImg.addClass(this.options.aIcons[content[this.mapToData]].class);
        }
        return newImg;
    }
};
$.extend(TableBuilderStatusCell, TableBuilderBaseCell);

// main table builder
var TableBuilder = {
    modecard: false,
    showcardmode: true,
    url: null, // url of function
    cols: null, // table columns definitions
    options: null,
    divid: null, //id of table <div>
    tableid: null, // table id,
    mainDiv: null, // table div object
    heads: null,
    bottoms: null,
    tableParams: null, // parameters posted to url
    paginationtd: null, // div for pagination
    paginationtd2 : null,
    filterdiv : null,
    toggletd: null,
    modeTableBtn: null,
    modeCardBtn: null,
    filterBtn: null,
    tableBody: null, // table body element
    tableFoot: null, // table footer element
    tableFootButtons: null,
    tableFootItemsPerPage: null,
    sortCells: {},
    defaultcolumn : null,
    defaultorder: null,
    tableHead: null, // table header element
    searchinput: null, // search input element
    selpage: null, // page selector element
    selectors: null, // selectors in selection line
    data: null, //data to be displayed
    iTotalLines: null, // total line number
    iCurrentPage: null, // current page
    iNbPages: null, //number of pages
    columns: [], // column objects
    columnnames: [],
    columnIndex: null, //column index
    lineIndex: null, //line index
    aTabsQueries: null, //xhr tab queries to cancel, for search engine
    colspan: null,

    // constructor
    /*
     @param string url url for table builder
     @param array cols table columns
     @param object options
     @param object params
     */
    init: function (element, url, cols, options, params) {
        this.url = url;
        this.cols = cols;
        this.options = options;
        this.divid = element.id;
        this.tableid = this.divid + '_table';
        this.mainDiv = $('#' + this.divid);
        this.tableParams = params;
        this.selectors = [];
        this.data = [];
        this.iTotalLines = 0;
        this.iNbPages = 0;
        this.columnIndex = -1;
        this.lineIndex = -1;
        this.aTabsQueries = new Array();
        this.showcardmode = this.options.showcardmode;
        this.modecard = this.options.modecard;
        this.setCurrentPage();
        this.defineColumns();
        if (this.showcardmode && this.options.automode){
            $( window ).on('resize', {self : this}, this.resize)
            this.adaptdisplay(false);
        } else {
            this.modecard = this.options.modecard;
        }
        this.display();
    },
    adaptdisplay: function(display){
        var oldmode = this.modecard;
        if (this.mainDiv.width() < this.options.breakpoint){
            this.modecard = true;
        } else{
            this.modecard = false;
        }
        if (display && oldmode != this.modecard){
            this.display();
        }
    },
    resize: function(e) {
        e.data.self.adaptdisplay(true);
    },
    setCurrentPage: function(){
        if (this.tableParams.start == 0){
            this.iCurrentPage = 1;
        } else {
            this.iCurrentPage = (this.tableParams.start / this.tableParams.itemsperpage) + 1;
        }
    },
    setUrl: function() {
        if (this.options.updateurl) {
            let url = window.location.origin + window.location.pathname + '?';
            let params = new URLSearchParams(window.location.search);
            if (params.has(this.options.getattribute)){
                var jObj = JSON.parse(params.get(this.options.getattribute));
            } else{
                var jObj = {};
            }
            jObj[this.divid] = this.buildParams();
            params.set(this.options.getattribute, JSON.stringify(jObj));
            history.pushState({}, "", url + params.toString());
        }
    }, 
    // image to be displayed on table load
    buildAjaxImg: function () {
        return '<em class="' + this.options.ajaximgname + '"></em>';
    },
    // reset search input on click
    resetsearchterm: function () {
        if (this.searchinput != null) {
            this.searchinput.val('');
        }
        this.tableParams.searchTerm = '';
        this.setUrl();
    },
    // reset table page to 1
    resettablepage: function () {
        if (this.selpage != null) {
            this.selpage.children().first().prop('selected', true);
        }
        this.iCurrentPage = 1;
        this.tableParams.start = 0;
    },
    // reset input click
    resetinputclick: function (event) {
        event.data.self.resetsearchterm();
        event.data.self.resettablepage();
        event.data.self.reload();
    },
    switchMode: function(e){
        self = e.data.self;
        self.modecard = e.data.modecard;
        if (self.modecard){
            self.modeTableBtn.show();
            self.modeCardBtn.hide();
        }else{
            self.modeTableBtn.hide();
            self.modeCardBtn.show();
        }
        self.display();
    },
    dofilterdivid: function() {
        if (this.options.filterdivid != undefined){
            return '#' + this.options.filterdivid;
        }else {
            return '#' + this.divid + '_filters';
        }
    },
    togglefilters: function(e){
        e.data.self.filterdiv.toggle();
    },
    printBeforeTable: function(){
        let headline = $('<tr></tr>');
        let table = $('<table></table>').attr('id', this.divid + '_beforetable').addClass(this.options.headersclass).append(headline);
        let inputCsrf = $('<input/>').attr('type', 'hidden').attr('id', this.tableid + '_csrf').val(this.options.csrf);
        this.heads = $('<form/>').attr('action', '').append(inputCsrf).append(table);
        this.paginationtd = $('<td></td>').attr('id', this.divid + '_pagination').addClass(this.options.paginationclass);
        this.toggletd = $('<td></td>').addClass(this.options.togglebtnclass);
        if (this.showcardmode){
            this.modeTableBtn = $('<em>').attr('id', this.divid + '_mode_table_btn')
                .addClass(this.options.modetablebtn).attr('title', this.options.tableModeLabel).on('click', {self: this, modecard: false}, this.switchMode);
            this.modeCardBtn = $('<em>').attr('id', this.divid + '_mode_card_btn').
                addClass(this.options.modecardbtn).attr('title', this.options.cardModeLabel).on('click', {self: this, modecard: true}, this.switchMode);
            if (this.modecard){
                this.modeCardBtn.hide();
            }else{
                this.modeTableBtn.hide();
            }
        }
        this.toggletd.append(this.modeTableBtn).append(this.modeCardBtn);
        if (this.options.buildfilters){
            this.filterBtn = $('<em/>').addClass(this.options.filterbtn).attr('title', this.options.togglefilters).on('click', {self: this}, this.togglefilters);;
            this.filterdiv = $(this.dofilterdivid());
            this.filterdiv.hide();
            this.heads.append(this.filterdiv);
            this.toggletd.append(this.filterBtn);
        }
        if (this.options.searchable) {
            this.searchinput = $('<input/>').attr('type', 'text').attr('id', this.divid + '_searchinput')
                    .attr('placeholder', this.options.searchLabel).addClass(this.options.searchinputclass).val(this.tableParams.searchTerm);
            this.searchinput.on('keyup', {self: this}, this.searchInputChange);
            let xicon = $('<em/>').addClass("fa-solid fa-circle-xmark");
            let resetButton = $('<button/>').addClass(this.options.searchresetbuttonclass).attr('type', 'button').attr('id', this.divid + '_searchresetbtn')
                    .attr('title', this.options.searchresetlabel).append(xicon).on('click', {self: this}, this.resetinputclick);
            let resetbuttondiv = $('<div/>').addClass(this.options.searchresetbuttondivclass).append(resetButton);
            let searchinputgrpclass = $('<div/>').addClass(this.options.searchinputgrpclass).append(this.searchinput).append(resetbuttondiv);
            let searchtd = $('<td></td>').attr('id', this.divid + '_search').addClass(this.options.searchdivclass).append(searchinputgrpclass);
            headline.append(searchtd);
        }
        headline.append(this.toggletd).append(this.paginationtd);
        this.mainDiv.append(this.heads);
    },
    printAfterTable: function(){
        let bottomline = $('<tr></tr>');
        this.bottoms = $('<table></table>').attr('id', this.divid + '_aftertable').addClass(this.options.bottomclass).append(bottomline);
        this.paginationtd2 = $('<td></td>').attr('id', this.divid + '_pagination2').addClass(this.options.paginationclass);
        this.tableFootButtons = $('<td></td>').attr('id', 'buttons_' + this.tableid);
        this.tableFootItemsPerPage  = $('<td></td>').attr('id', this.tableid + '_itemsperpage').addClass(this.options.pagecontclass);
        bottomline.append(this.tableFootButtons);
        bottomline.append(this.tableFootItemsPerPage);
        bottomline.append(this.paginationtd2);
        this.mainDiv.append(this.bottoms);
    },
    displayTable: function(){
        this.printBeforeTable();
        let newtable = $('<table></table>');
        newtable.attr('id', this.tableid);
        newtable.addClass(this.options.tableClass);
        this.tableBody = $('<tbody></tbody>');
        this.tableHead = $('<thead></thead>');
        let tableFoot = $('<tfoot></tfoot>');
        let newline = $('<tr></tr>').attr('id', this.tableid + '_line_footer');
        this.tableFoot = $('<td></td>').attr('colspan', this.colspan).addClass(this.options.footerclass).appendTo(newline);
        newline.appendTo(tableFoot);
        newtable.append(this.tableHead);
        newtable.append(tableFoot);
        newtable.append(this.tableBody);
        this.buildHead();
        this.mainDiv.append(newtable);
        this.printAfterTable();
        this.doItemsPerPageChoice();
    },
    displayCards : function () {
        this.printBeforeTable();
        this.tableBody = $('<div/>').attr('id', this.tableid).addClass(this.options.cardgroupclass);
        this.tableFoot = $('<div/>').addClass(this.options.cardfooter);
        this.mainDiv.append(this.tableBody).append(this.tableFoot);
        this.printAfterTable();
        this.doItemsPerPageChoice();
    },
    // builds table
    display: function () {
        this.mainDiv.html('');
        if (this.modecard){
            this.displayCards();
        } else {
            this.displayTable();
        }
        this.resetOrder(true);
        this.reload();
    },
    // reload table data
    reload: function () {
        this.loadDataByAjax();
    },
    // event on items per page change
    doItemsPerPageChoice: function () {
        if (
                this.options.itemsperpage > 0 &&
                this.options.pagechoices != undefined &&
                this.options.pagechoices.length > 0
        ) {
            let newsel2 = $('<select></select>').attr('id', this.tableid + '_selEltsPerpage').addClass('form-control input-sm');
            for (var j = 0; j < this.options.pagechoices.length; j++) {
                let str = this.options.pagechoices[j];
                let newopt2 = $('<option></option>').attr('value', str).html(str);
                if (str == this.tableParams.itemsperpage) {
                    newopt2.attr('selected', 'selected');
                }
                newsel2.append(newopt2);
            }
            newsel2.on('change', {self: this}, this.changeItemsPerPage);
            this.tableFootItemsPerPage.append(newsel2);
            let lbl = $('<span></span>').html(this.options.eltsParPageLabel);
            this.tableFootItemsPerPage.append(lbl);
        }
    },
    // event on search input change everytime a character is added/removes
    searchInputChange: function (event) {
        //if (event.data.self.searchinput.val().length > 0){
        event.data.self.tableParams.searchTerm = event.data.self.searchinput.val();
        event.data.self.resettablepage();
        event.data.self.setUrl();
        event.data.self.reload();
        //}
    },
    // builds columns object
    defineColumns: function () {
        if (this.cols.length > 0) {
            for (var i = 0; i < this.cols.length; i++) {
                switch (this.cols[i].type) {
                    case 'data':
                        var obj = Object.create(TableBuilderDataCell);
                        break;
                    case 'action':
                        var obj = Object.create(TableBuilderActionCell);
                        break;
                    case 'checkbox':
                        var obj = Object.create(TableBuilderCheckboxCell);
                        break;
                    case 'color':
                        var obj = Object.create(TableBuilderColorCell);
                        break;
                    case 'date':
                        var obj = Object.create(TableBuilderDateCell);
                        break;
                    case 'image':
                        var obj = Object.create(TableBuilderImageCell);
                        break;
                    case 'link':
                        var obj = Object.create(TableBuilderLinkCell);
                        break;
                    case 'mail':
                        var obj = Object.create(TableBuilderMailCell);
                        break;
                    case 'numeric':
                        var obj = Object.create(TableBuilderNumericCell);
                        break;
                    case 'status':
                        var obj = Object.create(TableBuilderStatusCell);
                        break;
                    default:
                        throw 'Unknown column type';
                }
                obj.init(this, this.cols[i].data, this.cols[i].options);
                this.columnIndex++;
                obj.attachOrder(this.columnIndex);
                this.columns[this.columnIndex] = obj;
                this.columnnames[obj.mapToData] = obj;
                this.addColumnOrder(obj);
                if (obj.options.defaultOrder != undefined && obj.options.defaultOrder != null){
                    this.defaultcolumn = obj.mapToData;
                    this.defaultorder = obj.options.defaultOrder == 'asc' ? ASC : DESC;
                }
            }
            this.colspan = (this.options.addselectline) ? this.columns.length + 1 : this.columns.length;
            if (this.defaultcolumn == null){
                this.defaultcolumn = this.cols[0].data;
                this.defaultorder = 1;
            }
        } else {
            throw 'No cols';
        }
    },
    addColumnOrder: function (obj) {
        if (obj.options.sortable == true) {
            this.sortCells[obj.mapToData] = [];
            if (obj.options.customAsc != undefined) {
                this.sortCells[obj.mapToData][ASC] = obj.options.customAsc;
            } else {
                this.sortCells[obj.mapToData][ASC] = obj.mapToData + ':asc';
            }
            if (obj.options.customDesc != undefined) {
                this.sortCells[obj.mapToData][DESC] = obj.options.customDesc;
            } else {
                this.sortCells[obj.mapToData][DESC] = obj.mapToData + ':desc';
            }
        }
    },
    getColumnOrder: function (field, order){
        if ( this.sortCells[field] != undefined && this.sortCells[field][order] != undefined){
            return this.sortCells[field][order];
        }
        return '';
    }, 
    // build table heads
    buildHead: function () {
        this.tableHead.html('');
        let newline = $('<tr></tr>');
        newline.attr('id', this.tableid + "_head")
        if (this.options.addselectline) {
            let newth = $('<th></th>');
            newth.attr('id', this.tableid + '_head_colselect');
            newth.attr('width', '20px');
            let newChk = $('<input/>').attr('id', this.tableid + '_chkbox_collchk');
            newChk.attr('type', 'checkbox').attr('title', this.options.chkheadlabel);
            newChk.on('click', {self: this}, this.onChkboxHeaderClick);
            newth.append(newChk);
            newline.append(newth);
        }
        for (var i = 0; i < this.columns.length; i++) {
            newline.append(this.columns[i].buildhead());
        }
        this.tableHead.append(newline);
    },
    // reset table order
    resetOrder: function (show) {
        if (show == undefined){
            show = false;
        }
        for (var i = 0; i < this.columns.length; i++) {
            this.columns[i].resetOrder();
        }
        if (show){
            let name = '';
            let sortorder = '';
            if (this.tableParams.sortBy != ''){
                let sortBy = this.tableParams.sortBy;
                sortBy = sortBy.split(';')[0].split(':');
                name = sortBy[0];
                sortorder = sortBy[1];     
            }
            if (name != '' && this.columnnames[name] != undefined &&  this.columnnames[name].options.sortable){
                this.columnnames[name].sortStatus = (sortorder == 'asc' ? ASC : DESC);
                this.columnnames[name].displayarrows();
            } else {
                this.columnnames[this.defaultcolumn].sortStatus = this.defaultorder
                this.columnnames[this.defaultcolumn].displayarrows();;
                this.tableParams.sortBy = this.getColumnOrder(this.defaultcolumn, this.defaultorder);
            }
        }
    },
    buildSelector: function(i){
        let newChk = $('<input/>').attr('id', this.tableid + '_chkboxln_' + i);
        newChk.addClass(this.tableid + '_lineChkBox');
        newChk.attr('type', 'checkbox').attr('title', this.options.selectelement);
        this.selectors.push(newChk);
        return newChk;
    },
    // build data lines
    printDataLine: function () {
        this.tableBody.html('');
        this.selectors = [];
        var trigger = '';
        if (this.options.rowcontextualtrigger != undefined && this.options.rowcontextualtrigger.length > 0) {
            trigger = this.options.rowcontextualtrigger;
        }
        for (var i = 0; i < this.data.length; i++) {
            let newline = $('<tr></tr>');
            if (trigger != '') {
                if (this.data[i][trigger] != undefined) {
                    newline.addClass(this.data[i][trigger]);
                }
            }
            newline.attr('id', this.tableid + '_line_' + i);
            if (this.options.addselectline) {
                let newtd = $('<td></td>');
                newtd.attr('id', this.tableid + '_dataline_' + i + '_chktd');
                newtd.append(this.buildSelector(i));
                newline.append(newtd);
            }
            for (var j = 0; j < this.columns.length; j++) {
                newline.append(this.columns[j].builddata(newline, i, this.data[i]));
            }
            newline.appendTo(this.tableBody);
        }
    },
    printDataCards : function() {
        this.tableBody.html('');
        var trigger = '';
        if (this.options.rowcontextualtrigger != undefined && this.options.rowcontextualtrigger.length > 0) {
            trigger = this.options.rowcontextualtrigger;
        }
        for (var i = 0; i < this.data.length; i++) {
            let newCard = null;
            if (this.options.customprintdatacard != undefined){
                newCard  = this.options.customprintdatacard(this.data[i], this, i, trigger);
            } else{
                newCard = $('<div></div>').addClass(this.options.cardcol);
                let newCardCard = $('<div></div>').addClass(this.options.cardclass);

                let newCardContent = $('<div></div>').addClass(this.options.cardcontentclass).attr('id', this.tableid + '_line_' + i);
                if (trigger != '') {
                    if (this.data[i][trigger] != undefined) {
                        newCardContent.addClass(this.data[i][trigger]);
                    }
                }
                var start = 0;
                if (this.options.addselectline) {
                    let chk = $('<span>').attr('id', this.tableid + '_dataline_' + i + '_chktd').addClass(this.options.cardselector).append(this.buildSelector(i));
                    newCardContent.append(chk).append(this.columns[0].builddata(newCardContent, i, this.data[i]));
                    start++;
                }
                for (var j = start; j < this.columns.length; j++) {
                    newCardContent.append(this.columns[j].builddata(newCardContent, i, this.data[i]));
                }
                newCardCard.append(newCardContent);
                newCard.append(newCardCard);
            }
            newCard.appendTo(this.tableBody);
        }
    },
    // build table footer
    printFooter: function (str) {
        this.tableFoot.html(str);
    },
    // event if column header is clicked
    onChkboxHeaderClick: function (event) {
        $('.' + event.data.self.tableid + '_lineChkBox').each(function (ln_num) {
            this.checked = event.currentTarget.checked;
        });
    },
    // select a new page to display
    selectpage: function (i) {
        this.iCurrentPage = i;
        this.tableParams.start = (this.iCurrentPage * this.options.itemsperpage) - this.options.itemsperpage;
        this.setUrl();
        this.reload();
    },
    // do pagination div
    doPagination: function () {
        if (this.options.itemsperpage > 0) {
            this.paginationtd.html('');
            this.paginationtd2.html('');
            let maingrp = $('<div></div>').addClass("btn-group btn-group-sm").attr('role', "group");
            this.iNbPages = Math.floor(this.iTotalLines / this.options.itemsperpage) +
                    (this.iTotalLines % this.options.itemsperpage > 0 ? 1 : 0);
            if (this.iCurrentPage > 2) {
                let dblleftbtn = $('<button></button>')
                        .attr('type', "button")
                        .addClass("btn btn-default")
                        .on('click', {self: this}, function (event) {
                            event.data.self.selectpage(1)
                        })
                        .append($('<em/>').addClass(this.options.dblleftarrow));
                maingrp.append(dblleftbtn);
            }
            if (this.iCurrentPage > 1) {
                let leftbtn = $('<button></button>')
                        .attr('type', "button")
                        .addClass("btn btn-default")
                        .on('click', {self: this}, function (event) {
                            event.data.self.selectpage(event.data.self.iCurrentPage - 1)
                        }).append($('<em/>').addClass(this.options.leftarrow));
                maingrp.append(leftbtn);
            }
            if (this.iNbPages > 1) {
                let selgrp = $('<div></div>').addClass("btn-group").attr('role', "group");
                this.selpage = $('<select></select>').attr('id', this.tableid + '_selpage').addClass('form-control input-sm');
                for (var i = 1; i <= this.iNbPages; i++) {
                    let newopt = $('<option></option>').attr('value', i).html(i);
                    if (i == this.iCurrentPage) {
                        newopt.attr('selected', 'selected');
                    }
                    this.selpage.append(newopt);
                }
                this.selpage.on('change', {self: this}, function (event) {
                    event.data.self.selectpage($(this).children('option:selected').val());
                });
                selgrp.append(this.selpage);
                maingrp.append(selgrp);
            }
            if (this.iCurrentPage < this.iNbPages) {
                let rightbtn = $('<button></button>')
                        .attr('type', "button")
                        .addClass("btn btn-default")
                        .on('click', {self: this}, function (event) {
                            event.data.self.selectpage(event.data.self.iCurrentPage + 1)
                        }).append($('<em/>').addClass(this.options.rightarrow));
                maingrp.append(rightbtn);
            }
            if (this.iNbPages - this.iCurrentPage >= 2) {
                let dblrightbtn = $('<button></button>')
                        .attr('type', "button")
                        .addClass("btn btn-default")
                        .on('click', {self: this}, function (event) {
                            event.data.self.selectpage(event.data.self.iNbPages)
                        }).append($('<em/>').addClass(this.options.dblrightarrow));
                maingrp.append(dblrightbtn);
            }
            let total = $('<span></span>').html(this.iTotalLines + ' ' + this.options.eltLabel)
            this.paginationtd.append(maingrp);
            this.paginationtd.append(total);
            this.paginationtd2.append(this.paginationtd.children().clone(true, true));
        }
    },
    // event if items per page changed
    changeItemsPerPage: function (e) {
        self = e.data.self;
        self.options.itemsperpage = $(this).children('option:selected').val();
        self.resettablepage();
        self.tableParams.itemsperpage = self.options.itemsperpage;
        if (self.options.eltsPerPageChngCallback != undefined) {
            self.options.eltsPerPageChngCallback(self.options.itemsperpage);
        }
        self.setUrl();
        self.reload();
    },
    applyaction: function(action) {
        for (var i = 0; i < this.selectors.length; i++) {
            if (this.selectors[i].prop('checked')) {
                action(this.data[i]);
            }
        }
    },
    putLoadImage: function(){
        this.tableBody.html('<tr><td colspan="' + this.colspan + '">' + this.buildAjaxImg() + '</td></tr>')
    },
    putErrorMessage: function(){
        if (this.modecard){
            this.tableBody.html('<p>' + this.options.ajaxerrormsg + '</p>');
        } else {
            this.tableBody.html('<tr><td colspan="' + this.colspan + '">' + this.options.ajaxerrormsg + '</td></tr>');
        }
    },
    processData: function(data){
        if (this.modecard ){
            this.addCards(data);
        } else {
            this.addTableLines(data);
        }
    },
    addCards: function(data){
        if (data.aLines.length > 0) {
            this.data = data.aLines;
            this.iTotalLines = data.iTotalLines;
            this.doPagination();
            this.printDataCards();
            if (
                data.sFooter !== undefined && (typeof data.sFooter == 'string' || data.sFooter instanceof String) &&
                data.sFooter.length > 0
            ) {
                this.printFooter(data.sFooter);
            }
            if (this.options.aftertableload != undefined) {
                this.options.aftertableload(this, data);
            }
        } else {
            this.tableBody.html('<div>' + this.options.nodatastr + '</div>');
        }
    },
    addTableLines: function(data){
        if (data.aLines.length > 0) {
            this.data = data.aLines;
            this.iTotalLines = data.iTotalLines;
            this.doPagination();
            this.printDataLine();
            if (
                data.sFooter !== undefined && (typeof data.sFooter == 'string' || data.sFooter instanceof String) &&
                data.sFooter.length > 0
            ) {
                this.printFooter(data.sFooter);
            }
            if (this.options.aftertableload != undefined) {
                this.options.aftertableload(this, data);
            }
        } else {
            this.tableBody.html('<tr><td colspan="' + this.colspan + '">' + this.options.nodatastr + '</td></tr>');
        }
    },
    buildParams: function() {
        let myParams = null;
        if (this.options.paramsFunction != null) {
            myParams = $.extend({}, this.tableParams, this.options.paramsFunction(), this.options.additionalParams);
        } else {
            myParams = $.extend({}, this.tableParams, this.options.additionalParams);
        }
        return myParams;
    },
    // load data
    loadDataByAjax: function () {
        var self = this;
        
        $.ajax({
            url: self.url,
            data: self.buildParams(),
            encoding: self.options.encoding,
            type: 'post',
            dataType: 'json',
            cache: false,
            headers: {
                'X-CSRF-Token': self.options.csrf
            },
            beforeSend: function (xhr) {
                let query = null;
                while (query = self.aTabsQueries.pop()) {
                    if (typeof query == 'object' && query.readyState < 4) {
                        query.abort();
                    }
                }
                self.aTabsQueries.push(xhr);
                self.putLoadImage();
            }
        }).done(function (data) {
            self.aTabsQueries.pop();
            self.processData(data);
        }).fail(function (jqXHR, textStatus, errorThrown) {
            if (jqXHR.status == 419) {
                location.reload();
            } else {
                self.putErrorMessage();
            }
        });
    }
};


if (typeof Object.create !== 'function') {
    Object.create = function (o) {
        function F() { } // optionally move this outside the declaration and into a closure if you need more speed.
        F.prototype = o;
        return new F();
    };
}
// table builder function
(function ($) {
    /*
     @param string url url for table builder
     @param array cols table columns
     @params object options
     */
    $.fn.tablebuilder = function (url, cols, options, params) {
        return this.each(function () {
            var element = $(this);
            if (element.prop('tagName') != 'DIV')
                throw 'not a DIV';
            // Return early if this element already has a plugin instance
            if (element.data('mytable'))
                return element.data('mytable');
            var mytable = Object.create(TableBuilder);
            mytable.init(this, url, cols, options, params);
            // pass options to plugin constructor
            element.data('tablebuilder', mytable);
        });
    };

})($);

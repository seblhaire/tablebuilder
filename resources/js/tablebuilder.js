/**
Extendable base class for table cells
 */
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
	sortStatus: 0,//-1 / +1
	//sub_constructor common to all cell types
	/*
	@param  TableBuilder table
	@param string mapToData data field
	@param object options
	*/
	_init: function(table, mapToData, options) {
		this.table = table;
		this.options = options;
		this.mapToData = mapToData;
		if (this.options == undefined) {
			throw 'options not defined';
		}
		this.setOrderOptions();

	},
	//set column order
	attachOrder: function(index) {
		this.cellIndex = index;
	},
	// build header cell
	buildhead: function() {
		let newth = jQuery('<th></th>');
		newth.attr('id', this.table.tableid + '_head_' + this.cellIndex).attr('scope', 'col');
		if (this.options.width != undefined) {
			newth.attr('width', this.options.width);
		}

		if (this.options.completetitle != undefined) {
			let newa = jQuery('<a></a>').text('\xa0'); //&nbsp;
			newa.attr('href', '#');
			newa.attr('title', this.options.completetitle);
			newa.on('click', function(e) { e.preventDefault; });
			newth.append(newa);
			newa.html(this.options.title);
		} else {
			if (this.options.sortable == true) {
				title = this.options.title + "&nbsp;";
			} else {
				title = this.options.title;
			}
			newth.html(title);
		}
		if (this.options.sortable == true) {
			newth.addClass('sortable');
			this.ascImg = jQuery('<em/>');
			this.ascImg.attr('id', this.table.tableid + '_head_' + this.cellIndex + '_asc');
			this.ascImg.addClass(this.table.options.uparrow);
			if (this.sortStatus != 1) {
				this.ascImg.attr('style', 'display:none');
			}
			this.descImg = jQuery('<em/>');
			this.descImg.attr('id', this.table.tableid + '_head_' + this.cellIndex + '_desc');
			this.descImg.addClass(this.table.options.downarrow);
			if (this.sortStatus != -1) {
				this.descImg.attr('style', 'display:none');
			}
			this.arrowspan = jQuery('<span></span>');
			this.arrowspan.addClass('arrows');
			this.arrowspan.append(this.ascImg);
			this.arrowspan.append(this.descImg);
			newth.append(this.arrowspan);
			newth.on('mouseover', { self: this }, this.onHeaderOver);
			newth.on('mouseout', { self: this }, this.onHeaderOut);
			newth.on('click', { self: this }, this.onHeaderClick);
		}
		return newth;
	},
	// display arrows function
	displayarrows: function() {
		this.arrowspan.show();
		if (this.sortStatus >= 0) {
			this.ascImg.show();
			this.descImg.hide();
		} else {
			this.ascImg.hide();
			this.descImg.show();
		}
	},
	// on mouse over
	onHeaderOver: function(event) {
		var self = event.data.self;
		self.displayarrows();
	},
	// on mouse out
	onHeaderOut: function(event) {
		var self = event.data.self;
		if (self.sortStatus == 0) {
			self.arrowspan.hide();
		}
	},
	// reset sort status
	resetOrder: function(first) {
		if (first == undefined) first = false;
		if (this.options.sortable == true) {
			if (first && this.options.defaultOrder) {
				this.arrowspan.show();
			} else {
				this.sortStatus = 0;
				this.arrowspan.hide();
			}
		}
	},
	// return order parameter to table
	setOrderQuery: function() {
		if (this.sortStatus == 1) {
			if (this.options.customAsc != undefined) {
				return this.options.customAsc;
			} else {
				return this.mapToData + ':asc';
			}
		} else if (this.sortStatus == -1) {
			if (this.options.customDesc != undefined) {
				return this.options.customDesc;
			} else {
				return this.mapToData + ':desc';
			}
		}
	},
	// set column sort statsu
	setOrderOptions: function() {
		if (this.options.defaultOrder == 'asc') {
			this.sortStatus = 1;
		} else if (this.options.defaultOrder == 'desc') {
			this.sortStatus = -1;
		} else {
			this.sortStatus = 0;
		}
	},
	// action on column header click
	onHeaderClick: function(event) {
		var self = event.data.self;
		event.preventDefault();
		oldstatus = self.sortStatus;
		self.table.resetOrder();
		self.table.resettablepage();
		if (oldstatus < 1) {
			self.sortStatus = 1;
		} else {
			self.sortStatus = -1;
		}
		self.table.tableParams.sortBy = self.setOrderQuery();
		self.displayarrows();
		self.table.reload();
	},
	// common cell building functions
	_buildCell: function(lineIndex) {
		this.cell = jQuery('<td></td>');
		this.cell.attr('id', this.table.tableid + '_dataline_' + lineIndex + '_' + this.cellIndex);
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
	init: function(table, mapToData, options) {
		this.type = 'DataCell';
		this._init(table, mapToData, options);
	},
	// build cell
	builddata: function(lineobj, lineIndex, content) {
		this.line = lineobj;
		this._buildCell(lineIndex);
		//console.log(this.cell);
		if (this.options.classes != undefined) {
			this.cell.addClass(this.options.classes);
		}
		this.cell.html(content[this.mapToData]);
		//console.log(this.cell);
		return this.cell;
	}
};
jQuery.extend(TableBuilderDataCell, TableBuilderBaseCell);

// Action(s) cell
var TableBuilderActionCell = {
	// constructor
	/*
	@param  TableBuilder table
	@param string mapToData data field
	@param object options
	*/
	init: function(table, mapToData, options) {
		this.type = 'ActionCell';
		this._init(table, mapToData, options);
	},
	// trigger action
	onActionClick: function(event) {
		var self = event.data.self;
		var data = event.data.data;
		var js = event.data.action.js;
		event.preventDefault();
		if (js instanceof Function){
			js(data);
		}else {
			js = window[js];
			js(data);
		}
	},
	// build action buttons
	buildActions: function(td, content, actionList) {
		for (var i = 0; i < actionList.length; i++) {
			let newa = jQuery('<a></a>').attr('href', '#');
			if (actionList[i].text != undefined) {
				newa.attr('title', actionList[i].text);
			}
			newa.on('click', { self: this, action: actionList[i], data: content }, this.onActionClick);
			let newimg = null;
			if (actionList[i].placeholder != undefined) {
				newimg = jQuery('<em></em>').attr('class', 'glyphicon').html('&nbsp;');
			} else if (actionList[i].em != undefined) {
				newimg = jQuery('<em></em>').attr('class', actionList[i].em);
			}
			newa.append(newimg);
			td.append(newa);
		}
	},
	// build cell
	builddata: function(lineobj, lineIndex, content) {
		this.line = lineobj;
		this._buildCell(lineIndex);
		if (this.options.classes != undefined) {
			this.cell.addClass(this.options.classes);
		}
		if (content[this.mapToData] instanceof Array) {
			this.buildActions(this.cell, content, content[this.mapToData]);
		} else if (this.options.actions instanceof Array) {
			this.buildActions(this.cell, content, this.options.actions);
		}
		return this.cell;

	},
	// replaces default head builder
	buildhead: function() {
		let newth = jQuery('<th></th>');
		newth.attr('id', this.table.tableid + '_head_' + this.cellIndex);
		if (this.options.width != undefined) {
			newth.attr('width', this.options.width);
		}

		if (this.options.completetitle != undefined) {
			let newa = jQuery('<a></a>').text('\xa0'); //&nbsp;
			newa.attr('href', '#');
			newa.attr('title', this.options.completetitle);
			newa.on('click', function(e) { e.preventDefault; });
			newth.append(newa);
			newa.html(this.options.title);
		} else {
			title = this.options.title;
			newth.html(title);
		}
		return newth;
	}
};
jQuery.extend(TableBuilderActionCell, TableBuilderBaseCell);

// checkbox cell
var TableBuilderCheckboxCell = {
	// constructor
	/*
	@param  TableBuilder table
	@param string mapToData data field
	@param object options
	*/
	init: function(table, mapToData, options) {
		this.type = 'CheckboxCell';
		this._init(table, mapToData, options);
	},
	// build cell
	builddata: function(lineobj, lineIndex, content) {
		this.line = lineobj;
		this._buildCell(lineIndex);
		if (this.options.classes != undefined) {
			this.cell.addClass(this.options.classes);
		}
		let newChk = jQuery('<input/>').attr('id', this.table.tableid + '_chkbox_' + lineIndex + '_' + this.cellIndex);
		newChk.attr('type', 'checkbox');
		if (typeof content[this.mapToData] === "boolean") {
			newChk.attr('checked', content[this.mapToData]);
		} else if (content[this.mapToData] == 1) {
			newChk.attr('checked', true);
		} else if (content[this.mapToData] == 0) {
			newChk.attr('checked', false);
		} else {
			//console.log(content[this.mapToData]);
			throw 'bad type';
		}
		if (this.options.isEnabledCallback != null) {
			if (!this.options.isEnabledCallback(this, content)) {
				newChk.attr('disabled', 'yes');
			}
		}
		newChk.on('click', { elt: newChk, action: this.options.action, content: content, index: lineIndex }, this.onInputClick);
		this.cell.append(newChk);
		return this.cell;
	},
	// action on input click
	onInputClick: function(event) {
		var action = event.data.action;
		if (action != null) {
			action(event);
		}
	}
};
jQuery.extend(TableBuilderCheckboxCell, TableBuilderBaseCell);

// date cell
var TableBuilderDateCell = {
	// constructor
	/*
	@param  TableBuilder table
	@param string mapToData data field
	@param object options
	*/
	init: function(table, mapToData, options) {
		this.type = 'DateCell';
		this._init(table, mapToData, options);
	},
	// build cell
	builddata: function(lineobj, lineIndex, content) {
		this.line = lineobj;
		this._buildCell(lineIndex);
		let datestr = '';
		if (content[this.mapToData] != undefined) {
			let date = content[this.mapToData];
			if (date.length == 10) {
				date += ' 00:00:00';
			}
			datestr = moment(date).format(this.options.format);
		}
		if (this.options.classes != undefined) {
			this.cell.addClass(this.options.classes);
		}
		this.cell.html(datestr);
		return this.cell;
	}
};
jQuery.extend(TableBuilderDateCell, TableBuilderBaseCell);

// image cell
var TableBuilderImageCell = {
	// constructor
	/*
	@param  TableBuilder table
	@param string mapToData data field
	@param object options
	*/
	init: function(table, mapToData, options) {
		this.type = 'ImageCell';
		this._init(table, mapToData, options);
	},
	// build cell
	builddata: function(lineobj, lineIndex, content) {
		this.line = lineobj;
		this._buildCell(lineIndex);
		if (this.options.classes != undefined) {
			this.cell.addClass(this.options.classes);
		}
		if (content[this.mapToData] != undefined) {
			let newImg = jQuery('<' + this.options.tag + '/>');
			if (this.options.tag == 'img') {
				newImg.attr('src', content[this.mapToData]);
			} else {
				newImg.addClass(content[this.mapToData]);
			}
			this.cell.append(newImg);
		}
		return this.cell;
	}
};
jQuery.extend(TableBuilderImageCell, TableBuilderBaseCell);

// link cell
var TableBuilderLinkCell = {
	// constructor
	/*
	@param  TableBuilder table
	@param string mapToData data field
	@param object options
	*/
	init: function(table, mapToData, options) {
		this.type = 'LinkCell';
		this._init(table, mapToData, options);
	},
	// build cell
	builddata: function(lineobj, lineIndex, content) {
		this.line = lineobj;
		this._buildCell(lineIndex);
		if (this.options.classes != undefined) {
			this.cell.addClass(this.options.classes);
		}
		if (content[this.mapToData] != undefined) {
			var text = content[this.mapToData];
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
			let newa = jQuery('<a></a>');
			newa.attr('href', content[this.mapToData]);
			newa.attr('target', this.options.target);
			newa.attr("rel", "noopener noreferrer");
			newa.attr('title', content[this.mapToData]);
			newa.html(text);
			this.cell.append(newa);
		}
		return this.cell;
	}
};
jQuery.extend(TableBuilderLinkCell, TableBuilderBaseCell);

// mail cell
var TableBuilderMailCell = {
	// constructor
	/*
	@param  TableBuilder table
	@param string mapToData data field
	@param object options
	*/
	init: function(table, mapToData, options) {
		this.type = 'MailCell';
		this._init(table, mapToData, options);
	},
	// build cell
	builddata: function(lineobj, lineIndex, content) {
		this.line = lineobj;
		this._buildCell(lineIndex);
		if (this.options.classes != undefined) {
			this.cell.addClass(this.options.classes);
		}
		if (content[this.mapToData] != undefined) {
			let newa = jQuery('<a></a>');
			if (this.options.copycell == true) {
				newa.attr('href', '#').attr('title', this.options.copytext).on('click', this.onclick);
			} else {
				newa.attr('href', 'mailto:' + content[this.mapToData]);
			}
			newa.html(content[this.mapToData]);
			this.cell.append(newa);
		}
		return this.cell;
	},
	// click mail cell
	onclick: function(event) {
		event.preventDefault;
		var $temp = jQuery("<input>");
		jQuery("body").append($temp);
		$temp.val(jQuery(event.target).text()).select();
		document.execCommand("copy");
		$temp.remove();
	}
};
jQuery.extend(TableBuilderMailCell, TableBuilderBaseCell);

// numeric cell
var TableBuilderNumericCell = {
	// constructor
	/*
	@param  TableBuilder table
	@param string mapToData data field
	@param object options
	*/
	init: function(table, mapToData, options) {
		this.type = 'NumericCell';
		this._init(table, mapToData, options);
	},
	// number formatter
	format: function(number) {
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
	// build cell
	builddata: function(lineobj, lineIndex, content) {
		this.line = lineobj;
		this._buildCell(lineIndex);
		if (this.options.classes != undefined) {
			this.cell.addClass(this.options.classes);
		}
		if (content[this.mapToData] != undefined) {
			let number = this.format(content[this.mapToData]);
			if (this.options.currency.length > 0) {
				let formatted = '';
				if (this.options.currencyposafter) {
					formatted += number + ' ' + this.options.currency;
					this.cell.html(formatted);
				} else {
					this.cell.append(jQuery('<span></span>').addClass('currency').html(this.options.currency));
					this.cell.append(jQuery('<span></span>').html(number));
				}
			} else {
				this.cell.html(number);
			}
		}
		return this.cell;
	}
};
jQuery.extend(TableBuilderNumericCell, TableBuilderBaseCell);

// status cell
var TableBuilderStatusCell = {
	// constructor
	/*
	@param  TableBuilder table
	@param string mapToData data field
	@param object options
	*/
	init: function(table, mapToData, options) {
		this.type = 'StatusCell';
		this._init(table, mapToData, options);
	},
	// build cell
	builddata: function(lineobj, lineIndex, content) {
		this.line = lineobj;
		this._buildCell(lineIndex);
		if (this.options.classes != undefined) {
			this.cell.addClass(this.options.classes);
		}
		if (content[this.mapToData] != undefined) {
			let newImg = jQuery('<em/>');
			newImg.attr('style', this.options.aIcons[content[this.mapToData]].style);
			newImg.attr('title', this.options.aIcons[content[this.mapToData]].title);
			newImg.addClass(this.options.aIcons[content[this.mapToData]].class);
			this.cell.append(newImg);
		}
		return this.cell;
	}
};
jQuery.extend(TableBuilderStatusCell, TableBuilderBaseCell);

// main table builder
var TableBuilder = {
	url: null, // url of function
	cols: null, // table columns definitions
	options: null,
	divid: null, //id of table <div>
	tableid: null, // table id
	mainDiv: null, // table div object
	tableParams: null, // parameters posted to url
	paginationDiv: null, // div for pagination
	tableBody: null, // table body element
	tableFoot: null, // table footer element
	tableHead: null, // table header element
	searchinput: null, // search input element
	selpage: null, // page selector element
	selectors: null, // selectors in selection line
	data: null, //data to be displayed
	iTotalLines: null, // total line number
	iCurrentPage: null, // current page
	iNbPages: null, //number of pages
	columns: null, // column objects
	columnIndex: null, //column index
	lineIndex: null, //line index
	aTabsQueries: null, //xhr tab queries to cancel, for search engine
	colspan: null,

	// constructor
	/*
	@param string url url for table builder
	@param array cols table columns
	@param object options
	*/
	init: function(element, url, cols, options) {
		this.url = url;
		this.cols = cols;
		this.options = options;
		this.divid = element.id;
		this.tableid = this.divid + '_table';
		this.mainDiv = jQuery('#' + this.divid);
		this.tableParams = {
			itemsperpage: this.options.itemsperpage,
			sortBy: null,
			start: 0,
			searchTerm: ''
		};
		this.selectors = [];
		this.data = [];
		this.iTotalLines = 0;
		this.iCurrentPage = 1;
		this.iNbPages = 0;
		this.columns = [];
		this.columnIndex = -1;
		this.lineIndex = -1;
		this.aTabsQueries = new Array();
		this.defineColumns();
		this.display();
	},
	// image to be displayed on table load
	buildAjaxImg: function() {
		return '<em class="' + this.options.ajaximgname + '"></em>';
	},
	// reset search input on click
	resetsearchterm: function() {
		if (this.searchinput != null) {
			this.searchinput.val('');
		}
		this.tableParams.searchTerm = '';
	},
	// reset table page to 1
	resettablepage: function() {
		if (this.selpage != null) {
			this.selpage.children().first().prop('selected', true);
		}
		this.iCurrentPage = 1;
		this.tableParams.start = 0;
	},
	// reset input click
	resetinputclick: function(event) {
		event.data.self.resetsearchterm();
		event.data.self.resettablepage();
		event.data.self.reload();
	},
	// builds table
	display: function() {
		let heads = jQuery('<div></div>').attr('id', this.divid + '_beforetable').addClass(this.options.headersclass);
		this.mainDiv.append(heads);
		this.paginationDiv = jQuery('<div></div>').attr('id', this.divid + '_pagination').addClass(this.options.paginationclass);
		heads.append(this.paginationDiv);
		let inputCsrf = jQuery('<input/>').attr('type', 'hidden').attr('id', this.tableid + '_csrf').val(this.options.csrf);
		let newForm = jQuery('<form/>').attr('action', '').append(inputCsrf);
		if (this.options.searchable) {
			this.searchinput = jQuery('<input/>').attr('type', 'text').attr('id', this.divid + '_searchinput')
				.attr('placeholder', this.options.searchLabel).addClass('form-control input-sm');
			this.searchinput.on('keyup', { self: this }, this.searchInputChange);
			let resetButton = jQuery('<button/>').addClass(this.options.searchresetbuttonclass).attr('type', 'button').attr('id', this.divid + '_searchresetbtn')
				.attr('title', this.options.searchresetlabel).html('x').on('click', { self: this }, this.resetinputclick);
			let resetbuttondiv = jQuery('<div/>').addClass(this.options.searchresetbuttondivclass).append(resetButton);
			let searchinputgrpclass = jQuery('<div/>').addClass(this.options.searchinputgrpclass).append(this.searchinput).append(resetbuttondiv);
			let searchdiv = jQuery('<div></div>').attr('id', this.divid + '_searchdiv').addClass(this.options.searchdivclass).append(searchinputgrpclass);
			newForm.append(searchdiv);
		}
		heads.append(newForm);
		let newtable = jQuery('<table></table>');
		newtable.attr('id', this.tableid);
		newtable.addClass(this.options.tableClass);
		this.tableBody = jQuery('<tbody></tbody>');
		this.tableHead = jQuery('<thead></thead>');
		this.tableFoot = jQuery('<tfoot></tfoot>');
		newtable.append(this.tableHead);
		newtable.append(this.tableFoot);
		newtable.append(this.tableBody);
		this.buildHead();
		this.mainDiv.append(newtable);
		this.doItemsPerPageChoice();
		this.doButtons();
		this.reload();
		this.resetOrder(true);
	},
	// reload table data
	reload: function() {
		this.loadDataByAjax();
	},
	// event on items per page change
	doItemsPerPageChoice: function() {
		if (
			this.options.itemsperpage > 0 &&
			this.options.pagechoices != undefined &&
			this.options.pagechoices.length > 0
		) {
			let nbPgs = jQuery('<div></div>').addClass(this.options.eltspageclass);
			let divP = jQuery('<div></div>').addClass(this.options.pagecontclass);
			let newsel2 = jQuery('<select></select>').attr('id', this.tableid + '_selEltsPerpage').addClass('form-control input-sm');
			for (var j = 0; j < this.options.pagechoices.length; j++) {
				let str = this.options.pagechoices[j];
				let newopt2 = jQuery('<option></option>').attr('value', str).html(str);
				if (str == this.options.itemsperpage) {
					newopt2.attr('selected', 'selected');
				}
				newsel2.append(newopt2);
			}
			newsel2.on('change', { self: this }, this.changeItemsPerPage);
			divP.append(newsel2);
			nbPgs.append(divP);
			let lbl = jQuery('<span></span>').html(' ' + this.options.eltsParPageLabel);
			divP.append(lbl);
			this.mainDiv.append(nbPgs);
		}
	},
	// builds button div for multiple actions
	doButtons: function() {
		if (this.options.buttons.length > 0) {
			let buttondiv = jQuery('<div></div>').attr('id', 'buttons_' + this.tableid);
			buttondiv.html('<i class="' + this.options.buttondivarrow + '"></i>');
			buttondiv.addClass(this.options.bottomclass);
			for (var i = 0; i < this.options.buttons.length; i++) {
				id = this.options.buttons[i].id != undefined ? this.options.buttons[i].id : 'button_' + this.tableid + '_' + i;
				let cl = this.options.buttons[i].class != undefined ? this.options.buttons[i].class : this.options.buttonclass;
				newbtn = jQuery('<button></button>').attr('id', id).attr('type', 'button').addClass(cl);
				if (this.options.buttons[i].title != undefined) {
					newbtn.attr('title', this.options.buttons[i].title);
				}
				content = '';
				if (this.options.buttons[i].img != undefined) {
					content += '<img src="' + this.options.buttons[i].img + '"/>';
				}
				if (this.options.buttons[i].em != undefined) {
					content += '<em class="' + this.options.buttons[i].em + '"></em>';
				}
				if (this.options.buttons[i].text != undefined) {
					content += ' ' + this.options.buttons[i].text;
				}
				let applyFunction = function(event) {
					data = event.data.table.data;
					selectors = event.data.table.selectors;
					action = event.data.action;
					for (var i = 0; i < selectors.length; i++) {
						if (selectors[i].prop('checked')) {
							action(data[i]);
						}
					}
				}
				newbtn.html(content).on('click', { table: this, action: this.options.buttons[i].action }, applyFunction);
				buttondiv.append(newbtn);
			}
			this.mainDiv.append(buttondiv);
		}
	},
	// event on search input change everytime a character is added/removes
	searchInputChange: function(event) {
		//if (event.data.self.searchinput.val().length > 0){
		event.data.self.tableParams.searchTerm = event.data.self.searchinput.val();
		event.data.self.resettablepage();
		event.data.self.reload();
		//}
	},
	// builds columns object
	defineColumns: function() {
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
				if (obj.options.defaultOrder != undefined) {
					this.tableParams.sortBy = obj.setOrderQuery();
				}
			}
			this.colspan = (this.options.buttons.length > 0) ? this.columns.length + 1 : this.columns.length;
		} else {
			throw 'No cols';
		}

	},
	// build table heads
	buildHead: function() {
		this.tableHead.html('');
		let newline = jQuery('<tr></tr>');
		newline.attr('id', this.tableid + "_head")
		if (this.options.buttons.length > 0) {
			let newth = jQuery('<th></th>');
			newth.attr('id', this.tableid + '_head_colselect');
			newth.attr('width', '20px');
			let newChk = jQuery('<input/>').attr('id', this.tableid + '_chkbox_collchk');
			newChk.attr('type', 'checkbox');
			newChk.on('click', { self: this }, this.onChkboxHeaderClick);
			newth.append(newChk);
			newline.append(newth);
		}
		for (var i = 0; i < this.columns.length; i++) {
			newline.append(this.columns[i].buildhead());
		}
		this.tableHead.append(newline);
	},
	// reset table order
	resetOrder: function(show) {
		for (var i = 0; i < this.columns.length; i++) {
			this.columns[i].resetOrder(show);
		}
	},
	// build data lines
	printDataLine: function() {
		this.tableBody.html('');
		var j = 0;
		this.selectors = [];
		var trigger = '';
		if (this.options.rowcontextualtrigger != undefined && this.options.rowcontextualtrigger.length > 0) {
			trigger = this.options.rowcontextualtrigger;
		}
		for (var i = 0; i < this.data.length; i++) {
			let newline = jQuery('<tr></tr>');
			if (trigger != '') {
				if (this.data[i][trigger] != undefined) {
					newline.addClass(this.data[i][trigger]);
				}
			}
			newline.attr('id', this.tableid + '_line_' + i);
			if (this.options.buttons.length > 0) {
				let newtd = jQuery('<td></td>');
				newtd.attr('id', this.tableid + '_dataline_' + i + '_chktd');
				let newChk = jQuery('<input/>').attr('id', this.tableid + '_chkboxln_' + i);
				newChk.addClass(this.tableid + '_lineChkBox');
				newChk.attr('type', 'checkbox');
				this.selectors.push(newChk);
				newtd.append(newChk);
				newline.append(newtd);
			}
			for (j = 0; j < this.columns.length; j++) {
				newline.append(this.columns[j].builddata(newline, i, this.data[i]));
			}
			newline.appendTo(this.tableBody);
		}
	},
	// build table footer
	printFooter: function(str) {
		this.tableFoot.html('');
		let newline = jQuery('<tr></tr>').attr('id', this.tableid + '_line_footer');
		let newtd = jQuery('<td></td>').attr('colspan', this.colspan).addClass(this.options.footerclass).html(str).appendTo(newline);
		newline.appendTo(this.tableFoot);
	},
	// event if column header is clicked
	onChkboxHeaderClick: function(event) {
		jQuery('.' + event.data.self.tableid + '_lineChkBox').each(function(ln_num) {
			this.checked = event.currentTarget.checked;
		});
	},
	// select a new page to display
	selectpage: function(i) {
		this.iCurrentPage = i;
		this.tableParams.start = (this.iCurrentPage * this.options.itemsperpage) - this.options.itemsperpage;
		this.reload();
	},
	// do pagination div
	doPagination: function() {
		if (this.options.itemsperpage > 0) {
			this.paginationDiv.html('');
			let maingrp = jQuery('<div></div>').addClass("btn-group btn-group-sm").attr('role', "group");
			this.iNbPages = Math.floor(this.iTotalLines / this.options.itemsperpage) +
				(this.iTotalLines % this.options.itemsperpage > 0 ? 1 : 0);
			if (this.iCurrentPage > 2) {
				let dblleftbtn = jQuery('<button></button>')
					.attr('type', "button")
					.addClass("btn btn-default")
					.on('click', { self: this }, function(event) { event.data.self.selectpage(1) })
					.append(jQuery('<em/>').addClass(this.options.dblleftarrow));
				maingrp.append(dblleftbtn);
			}
			if (this.iCurrentPage > 1) {
				let leftbtn = jQuery('<button></button>')
					.attr('type', "button")
					.addClass("btn btn-default")
					.on('click', { self: this }, function(event) {
						event.data.self.selectpage(event.data.self.iCurrentPage - 1)
					}).append(jQuery('<em/>').addClass(this.options.leftarrow));
				maingrp.append(leftbtn);
			}
			if (this.iNbPages > 1) {
				let selgrp = jQuery('<div></div>').addClass("btn-group").attr('role', "group");
				this.selpage = jQuery('<select></select>').attr('id', this.tableid + '_selpage').addClass('form-control input-sm');
				for (var i = 1; i <= this.iNbPages; i++) {
					let newopt = jQuery('<option></option>').attr('value', i).html(i);
					if (i == this.iCurrentPage) {
						newopt.attr('selected', 'selected');
					}
					this.selpage.append(newopt);
				}
				this.selpage.on('change', { self: this }, function(event) {
					event.data.self.selectpage(jQuery(this).children('option:selected').val());
				});
				selgrp.append(this.selpage);
				maingrp.append(selgrp);
			}
			if (this.iCurrentPage < this.iNbPages) {
				let rightbtn = jQuery('<button></button>')
					.attr('type', "button")
					.addClass("btn btn-default")
					.on('click', { self: this }, function(event) {
						event.data.self.selectpage(event.data.self.iCurrentPage + 1)
					}).append(jQuery('<em/>').addClass(this.options.rightarrow));
				maingrp.append(rightbtn);
			}
			if (this.iNbPages - this.iCurrentPage >= 2) {
				let dblrightbtn = jQuery('<button></button>')
					.attr('type', "button")
					.addClass("btn btn-default")
					.on('click', { self: this }, function(event) {
						event.data.self.selectpage(event.data.self.iNbPages)
					}).append(jQuery('<em/>').addClass(this.options.dblrightarrow));
				maingrp.append(dblrightbtn);
			}
			this.paginationDiv.append(maingrp);
			this.paginationDiv.append(jQuery('<span></span>').html(this.iTotalLines + ' ' + this.options.eltLabel));
		}
	},
	// event if items per page changed
	changeItemsPerPage: function(e) {
		self = e.data.self;
		self.options.itemsperpage = jQuery(this).children('option:selected').val();
		self.resettablepage();
		self.tableParams.itemsperpage = self.options.itemsperpage;
		if (self.options.eltsPerPageChngCallback != undefined) {
			self.options.eltsPerPageChngCallback(self.options.itemsperpage);
		}
		self.reload();
	},
	// load data
	loadDataByAjax: function() {
			var self = this;
			let myParams = null;
			if (self.options.paramsFunction != null) {
				let paramsFn = self.options.paramsFunction();
				myParams = jQuery.extend({}, self.tableParams, paramsFn, self.options.additionalParams);
			} else {
				myParams = jQuery.extend({}, self.tableParams, self.options.additionalParams);
			}
			jQuery.ajax({
				url: self.url,
				data: myParams,
				encoding: self.options.encoding,
				type: 'post',
				dataType: 'json',
				cache: false,
				headers: {
					'X-CSRF-Token': jQuery('#' + self.tableid + '_csrf').val()
				},
				beforeSend: function(xhr) {
					let query = null;
					while (query = self.aTabsQueries.pop()) {
						if (typeof query == 'object' && query.readyState < 4) {
							query.abort();
						}
					}
					self.aTabsQueries.push(xhr);
					self.tableBody.html('<tr><td colspan="' + self.colspan  + '">' + self.buildAjaxImg() + '</td></tr>');
				}
			}).done(function( data ) {
				self.aTabsQueries.pop();
				if (data.aLines.length > 0) {
					self.data = data.aLines;
					self.iTotalLines = data.iTotalLines;
					self.doPagination();
					self.printDataLine();
					let width = (self.options.buttons.length > 0) ? self.columns.length + 1 : self.columns.length;
					if (
						data.sFooter !== undefined && (typeof data.sFooter == 'string' || data.sFooter instanceof String) &&
						data.sFooter.length > 0
					) {
						self.printFooter(data.sFooter);
					}
					if (self.options.aftertableload != undefined) {
						self.options.aftertableload(this, data);
					}
				} else {
					self.tableBody.html('<tr><td colspan="' + self.colspan  + '">' + self.options.nodatastr + '</td></tr>');
				}
			}).fail(function(jqXHR, textStatus, errorThrown) {
				if (jqXHR.status == 419){
					location.reload();
				} else {
					self.tableBody.html('<tr><td colspan="' + self.colspan + '">' + self.options.ajaxerrormsg + '</td></tr>');
				}
			});
	}
};


if (typeof Object.create !== 'function') {
	Object.create = function(o) {
		function F() { } // optionally move this outside the declaration and into a closure if you need more speed.
		F.prototype = o;
		return new F();
	};
}
// table builder function
(function(jQuery) {
	/*
	@param string url url for table builder
	@param array cols table columns
	@params object options
	*/
	jQuery.fn.tablebuilder = function(url, cols, options) {
		return this.each(function() {
			var element = jQuery(this);
			if (element.prop('tagName') != 'DIV') throw 'not a DIV';
			// Return early if this element already has a plugin instance
			if (element.data('mytable')) return element.data('mytable');
			var mytable = Object.create(TableBuilder);
			mytable.init(this, url, cols, options);
			// pass options to plugin constructor
			element.data('mytable', mytable);
		});
	};

})(jQuery);

import { DataTable, showError, showSuccess } from './DataTable'
import Columns from './Columns'

class AccessLevel extends DataTable {
  constructor(data){
    super(data);

    this.pageSort = "levelName";//data.columns.data[1].name;

    this.hasSearch = false;
    this.hasPagination = false;
  }

  get getURL(){ return this.apiURL+'/all/'+ this.orgID + '/'; }
  get searchURL(){ return this.apiURL+'/all/'+ this.orgID + '/'; }

  cellRenderer(value, row, index, field) {
    if(field == 'order') {
      switch(value){
        case 0: return 'Very high'; break;
        case 1: return 'High'; break;
        case 2: return 'Medium'; break;
        case 3: return 'Low'; break;
        case 4: return 'Very low'; break;
      }
    }

    return super.cellRenderer(value, row, index, field);
  }

  rowActions(value, row, index, field) {
    if(row.order<4 ) return;
    return super.rowActions(value, row, index, field);
  }
}

var columns = [
  { name: 'order',display: 'Priority', hidden: false, editable: false, passData: false, width:'100px' },
  { name: 'levelName', display: 'Name', sortable: false, search: false },
  { name: 'id', primary: true, hidden: true },
];
var accesslevelColumns = new Columns(columns);

var data = {
  columns: accesslevelColumns,
  apiURL: apiURL + '/api/dashboard/level'
}

if($("#accesslevel").length != 0){
  var table = new AccessLevel(data);
  table.createTable('accesslevel');
}

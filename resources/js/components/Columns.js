export default class Columns {
  constructor(data){
    this.data = data;
  }

  get primaryColumn() {
    for(var x in this.data) {
      if(this.data[x].primary === true) return this.data[x].name;
    }

    return this.data[0].name;
  }

  get searchColumn() {
    for(var x in this.data) {
      if(this.data[x].search === true) return this.data[x].name;
    }

    return 'allFields';
  }

  get reservedColumn() {
    for(var x in this.data) {
      if(this.data[x].reserved === true) return this.data[x].name;
    }

    return null;
  }

  get ownershipColumn(){
    for(var x in this.data){
      if(this.data[x].ownership === true) return this.data[x].name;
    }
  }

  get names(){
    var names = [];
    for (var x in this.data){
      names.push(this.data[x].name);
    }

    return names;
  }

  getColumnByName(name){
    for(var x in this.data){
      if(this.data[x].name == name) return this.data[x];
    }
  }
}

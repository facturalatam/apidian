<template>
  <div class="card">
    <div class="card-header">Lista de Empresa</div>
    <div class="card-body">
     
      
          <el-table :data="tableData" style="width: 100%">
            <el-table-column prop="key" label="#" width="80"></el-table-column>
            <el-table-column prop="identification_number" label="Numero Identificacion" width="180"></el-table-column>
            <el-table-column prop="name" label="Empresa" width="180"></el-table-column>
            <el-table-column prop="email" label="email" width="180"></el-table-column>
            <el-table-column prop="created_at" label="Fecha" width="180"></el-table-column>
            <el-table-column prop="token" label="Token" width="220"></el-table-column>
          </el-table>
       
      
    </div>
  </div>
</template>
<style>
.extend {
  width: 100%;
}
</style>
<script>
export default {
  components: {},
  data() {
    return {
      hostname: window.location.hostname,
      resource: "configuration",
      tableData: []
    };
  },
  created() {
    this.getRecords();
  },
  methods: {
    getFilterRecord(array) {
      return array.filter(function(x) {
        return x.identification_number != null;
      });
    },
    getRecords() {
      return new Promise((resolve, reject) => {
        this.$http
          .get(`/${this.resource}/records`)
          .then(response => {
            this.tableData = this.getFilterRecord(response.data.data);
          })
          .catch(error => {})
          .then(() => {});
      });
    }
  }
};
</script>

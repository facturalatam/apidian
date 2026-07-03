<template>
  <div class="card">
    <div class="card-header">Lista de Impuesto</div>
    <div class="card-body">
      <el-table :data="tableData" stripe style="width: 100%">
        <el-table-column prop="id" label="#" width="180"></el-table-column>
        <el-table-column prop="name" label="Nombre" width="220"></el-table-column>
        <el-table-column prop="description" label="Description" width="220"></el-table-column>
        <el-table-column prop="code" label="Code" width="220"></el-table-column>
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
      resource: "tax",
      tableData: []
    };
  },
  created() {
    this.getRecords();
  },
  methods: {

 
    getRecords() {
      return new Promise((resolve, reject) => {
        this.$http
          .get(`/${this.resource}/records`)
          .then(response => {
            
            this.tableData = response.data
          })
          .catch(error => {})
          .then(() => {});
      });
    }
  }
};
</script>

<template>
  <div class="card">
    <div class="card-header">Lista de Documentos</div>

    <div class="card-body">
      <el-table :data="tableData" style="width: 100%">
        <el-table-column prop="key" label="#" width="80"></el-table-column>
        <el-table-column prop="number" label="Numero" width="100"></el-table-column>
        <el-table-column prop="client" label="Cliente" width="180"></el-table-column>
        <el-table-column prop="currency" label="Moneda" width="180"></el-table-column>
        <el-table-column prop="date" label="Fecha" width="180"></el-table-column>
        <el-table-column prop="sale" label="Venta" width="150"></el-table-column>
        <el-table-column prop="total_discount" label="Descuento" width="150"></el-table-column>
        <el-table-column prop="total_tax" label="Impuesto" width="150"></el-table-column>
        <el-table-column prop="subtotal" label="Sub Total" width="150"></el-table-column>
        <el-table-column prop="total" label="Total" width="150"></el-table-column>
        <el-table-column fixed="right" label="XML" width="120">
          <template slot-scope="scope">

            <a :href="`${resource}/downloadxml/${scope.row.xml}`" target="_blank" class="btn btn-xs btn-info waves-effect waves-light"><i class="fa fa-download"></i></a>
          
          </template>
        </el-table-column>
        <el-table-column fixed="right" label="PDF" width="120">
          <template slot-scope="scope">
           <a :href="`${resource}/downloadpdf/${scope.row.pdf}`" target="_blank" class="btn btn-xs btn-info waves-effect waves-light"><i class="fa fa-download"></i></a>
          </template>
        </el-table-column>
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
      resource: "documents",
      tableData: []
    };
  },
  created() {
      this.getRecords();
  },
  methods: {
    clickDownload(format) {
     /* window.open(
        `/${this.resource}/download/${this.form.external_id}/${format}`,
        "_blank"
      );*/
    },
   
    getRecords() {
      return new Promise((resolve, reject) => {
        this.$http
          .get(`/${this.resource}/records`)
          .then(response => {
            this.tableData = response.data.data
          })
          .catch(error => {})
          .then(() => {});
      });
    }
  }
};
</script>

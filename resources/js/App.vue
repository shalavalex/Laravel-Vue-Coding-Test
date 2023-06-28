<template>
  <div>
    <data-table></data-table>
  </div>
</template>

<script>
import { onMounted } from 'vue';
import DataTable from './components/DataTable.vue';
import axios from 'axios';

export default {
  components: {
    DataTable,
  },
  setup() {
  },
};
</script>
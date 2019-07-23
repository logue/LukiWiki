<template>
  <b-container fluid>
    <b-col
      md="6"
      class="my-1"
    >
      <b-form-group
        label-cols-sm="3"
        label="Filter"
        class="mb-0"
      >
        <b-input-group>
          <b-form-input
            v-model="filter"
            placeholder="Type to Search"
          />
          <b-input-group-append>
            <b-button
              :disabled="!filter"
              @click="filter = ''"
            >
              Clear
            </b-button>
          </b-input-group-append>
        </b-input-group>
      </b-form-group>
    </b-col>
    <b-col
      md="6"
      class="my-1"
    >
      <b-button @click="update">
        Update
      </b-button>
    </b-col>
    <b-table
      :items="items"
      :fields="fields"
      :busy="isBusy"
      :current-page="currentPage"
      :per-page="perPage"
      :filter="filter"
      :sort-by.sync="sortBy"
      :sort-desc.sync="sortDesc"
      :sort-direction="sortDirection"
      class="mt-3 mx-0"
      outlined
      @filtered="onFiltered"
    >
      <template
        slot="name"
        slot-scope="row"
      >
        <a
          :href="row.item.url"
          target="_blank"
        >{{ row.item.name }}
        </a><br>
        <small>{{ row.item.description }}</small>
      </template>
      <template
        slot="author"
        slot-scope="row"
      >
        <span
          v-for="person in row.item.author"
          :key="person.id"
        >
          <a
            v-if="typeof(person.homepage) !== 'undefined'"
            :href="person.homepage"
          >
            {{ person.name }}
          </a>
          <a
            v-else-if="typeof(person.email) !== 'undefined'"
            :href="'mailto:'+person.email"
          >
            <font-awesome-icon
              far
              icon="envelope"
            />
            {{ person.name }}</a>
          <span v-else>{{ person.name }}</span>,
        </span>
      </template>
      <div
        slot="table-busy"
        class="text-center text-danger my-2"
      >
        <b-spinner class="align-middle" />
        <strong>Loading...</strong>
      </div>
    </b-table>
    <b-row>
      <b-col
        md="6"
        class="my-1"
      >
        <b-pagination
          v-model="currentPage"
          :total-rows="totalRows"
          :per-page="perPage"
          class="my-0"
        />
      </b-col>
      <b-col
        md="6"
        class="my-1"
      >
        <b-form-group
          label-cols-sm="3"
          label="Per page"
          class="mb-0"
        >
          <b-form-select
            v-model="perPage"
            :options="pageOptions"
          />
        </b-form-group>
      </b-col>
    </b-row>
    <b-modal
      v-model="outputModal"
      title="Composer Output"
      no-close-on-backdrop
    >
      <div v-if="!outputBody">
        <b-spinner class="align-middle" />
      </div>
      <pre v-else>
        {{ outputBody }}
      </pre>
    </b-modal>
  </b-container>
</template>
<script>
export default {
  data() {
    return {
      totalRows: 1,
      currentPage: 1,
      perPage: 10,
      pageOptions: [10, 15, 20],
      sortBy: null,
      sortDesc: false,
      sortDirection: 'asc',
      filter: null,
      isBusy: true,
      fields: {
        name: {
          label: 'Package Name',
          sortable: true
        },
        version:{
          label:'Version'
        },
        author: {
          label: 'Author',
          sortable: true
        },
      },
      outputModal : false,
      outputBody: null,
      items:[]
    };
  },
  computed: {
    sortOptions() {
      // Create an options list from our fields
      return this.fields
        .filter(f => f.sortable)
        .map(f => {
          return { text: f.label, value: f.key };
        });
    }
  },
  async created() {
    try {
      const res = await window.axios.get('/:api/composer');
      this.items = res.data;
      this.totalRows = this.items.length;
      this.isBusy = false;
    } catch (e) {
      console.error(e);
    }
  },
  methods:{
    onFiltered(filteredItems) {
      // Trigger pagination to update the number of buttons/pages due to filtering
      this.totalRows = filteredItems.length;
      this.currentPage = 1;
    },
    async update(name = ''){
      const params = {
        command: 'update',
        option:name,
      };
      this.outputModal = true;
      try {
        const res = await window.axios.post('/:api/composer',params);
        this.outputBody = res.data;
      } catch (e) {
        console.error(e);
      }
    }
  }
};
</script>
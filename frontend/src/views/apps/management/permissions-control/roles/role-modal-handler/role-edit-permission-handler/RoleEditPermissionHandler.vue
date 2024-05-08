<template>
  <div>
    <!-- PERMISSION TABLE -->
    <b-card
      no-body
      class="border mt-1"
    >
      <b-card-header class="p-1">
        <b-card-title class="font-medium-2">
          <feather-icon
            icon="LockIcon"
            size="18"
          />
          <span class="align-middle ml-50">{{ $t('role.roleEditPermissionHandler.permissions') }}</span>
        </b-card-title>
      </b-card-header>
      <b-table
        ref="refUserListTable"
        class="position-relative"
        :items="permissions"
        responsive
        :fields="tableColumns"
        primary-key="id"
        show-empty
        :empty-text="$t('role.roleEditPermissionHandler.noPermissionFound')"
      >
        <template #cell(select)="data">
          <b-form-checkbox 
          :checked="data.item.hasPermission"
          @change="$emit('update-permission-role', data.item)"
          />
        </template>
      </b-table>
    </b-card>
  </div>
</template>

<script>
import {
  BButton, BMedia, BAvatar, BRow, BCol, BFormGroup, BFormInput, BForm, BTable, BCard, BCardHeader, BCardTitle, BFormCheckbox,
} from 'bootstrap-vue'
import { avatarText } from '@core/utils/filter'
import vSelect from 'vue-select'
import { useInputImageRenderer } from '@core/comp-functions/forms/form-utils'
import { ref } from '@vue/composition-api'

export default {
  components: {
    BButton,
    BMedia,
    BAvatar,
    BRow,
    BCol,
    BFormGroup,
    BFormInput,
    BForm,
    BTable,
    BCard,
    BCardHeader,
    BCardTitle,
    BFormCheckbox,
    vSelect,
  },
  props: {
    permissions: {
      type: Array,
      required: true,
    },
  },
  setup(props) {

    const tableColumns = [
      { key: 'select', label:'Selecionar' },
      { key: 'per_name', label: 'Tag', sortable: false },
      { key: 'per_description', label: 'Descrição', sortable: false },      
    ]

    // ? Demo Purpose => Update image on click of update
    const refInputEl = ref(null)
    const previewEl = ref(null)

    const { inputImageRenderer } = useInputImageRenderer(refInputEl, base64 => {
      // eslint-disable-next-line no-param-reassign
      props.userData.avatar = base64
    })

    return {
      avatarText,

      tableColumns,

      //  ? Demo - Update Image on click of update button
      refInputEl,
      previewEl,
      inputImageRenderer,
    }
  },
}
</script>

<style lang="scss">
@import '@core/scss/vue/libs/vue-select.scss';
</style>

<template>
    <div>
        <!-- A função handleSubmit só deixa a o formulário ser submetido (só chama a função onSubmit) caso todos os campos do form satisfação os os pré-requisitos -->
        
    <component :is="resources === undefined ? 'div' : 'b-card'">

      <!-- Alert: No item found -->
      <b-alert
        variant="danger"
        :show="resources === undefined"
      >
        <h4 class="alert-heading">
          Error fetching user data
        </h4>
        <div class="alert-body">
          No user found with this user id. Check
          <b-link
            class="alert-link"
            :to="{ name: 'apps-users-list'}"
          >
            User List
          </b-link>
          for other users.
        </div>
      </b-alert>

      <b-tabs
        v-if="resources"
        vertical
        content-class="col-12 col-md-9 mt-1 mt-md-0"
        pills
        nav-wrapper-class="col-md-3 col-12"
        nav-class="nav-left"
      >
        <span
          v-for="resource in resources"
          :key="resource.id"
        >
          <!-- Tab: Account -->
          <b-tab active>
            <template #title>
              <!--
              <feather-icon
                icon="UserIcon"
                size="16"
                class="mr-0 mr-sm-50"
              />
              -->
              <span class="d-none d-sm-inline text-capitalize">{{ resource.res_name }}</span>
            </template>
            <role-edit-permission-handler
              :permissions="resource.permissions"
              @update-permission-role="updatePermissionRole"
              class="mt-2 pt-75"
            />
          </b-tab>
        </span>
        
      </b-tabs>
    </component>
  </div>
  
</template>

<script>
import {
  BButton, BModal, VBModal, BForm, BFormInput, BFormGroup, BFormInvalidFeedback, BFormDatepicker, BFormCheckbox,
  BTab, BTabs, BLink, BAlert, BCard,
} from 'bootstrap-vue'
import axios from '@axios'
import store from '@/store'
import vSelect from 'vue-select'
import Ripple from 'vue-ripple-directive'
import { ref, toRefs } from '@vue/composition-api'
import { ValidationProvider, ValidationObserver } from 'vee-validate'
import useRoleModalHandler from './useRoleModalHandler'
import RoleEditPermissionHandler from './role-edit-permission-handler/RoleEditPermissionHandler.vue'
import { required } from '@validations'
import formValidation from '@core/comp-functions/forms/form-validation'
import VuePhoneNumberInput from 'vue-phone-number-input'
// Notification
import { useToast } from 'vue-toastification/composition'
import ToastificationContent from '@core/components/toastification/ToastificationContent.vue'

export default {
  components: {
    BButton,
    BModal,
    BForm,
    BFormInput,
    BFormGroup,
    vSelect,
    BFormInvalidFeedback,
    BFormDatepicker,
    BFormCheckbox,
    BTab,
    BTabs,
    BLink,
    BAlert,
    BCard,

    // Form Validation
    ValidationProvider,
    ValidationObserver,

    //Phone Input
    VuePhoneNumberInput,
    RoleEditPermissionHandler,
  },
  directives: {
    'b-modal': VBModal,
    Ripple,
  },
  props: {
    role: {
      type: Object,
      required: true,
    },
    refetchData: {
      type: Function,
      required: true,
    },
  },
  data() {
    return {
      //resources: [],
    }
  },
  methods: {
    
  },
  created() {
    //Traz os usuários cadastrados
    /*axios
      .get('/api/management/permission-control/resource/get-resources/'+this.role.id)
      .then(response => {
        //console.log(response.data)
        this.resources = response.data
      })*/
  },
  setup(props,{ emit }) {
    /*
     ? This is handled quite differently in SFC due to deadlock of `useFormValidation` and this composition function.
     ? If we don't handle it the way it is being handled then either of two composition function used by this SFC get undefined as one of it's argument.
     * The Trick:

     * We created reactive property `clearFormData` and set to null so we can get `resetEventLocal` from `useCalendarEventHandler` composition function.
     * Once we get `resetEventLocal` function which is required by `useFormValidation` we will pass it to `useFormValidation` and in return we will get `clearForm` function which shall be original value of `clearFormData`.
     * Later we just assign `clearForm` to `clearFormData` and can resolve the deadlock. 😎

     ? Behind The Scene
     ? When we passed it to `useCalendarEventHandler` for first time it will be null but right after it we are getting correct value (which is `clearForm`) and assigning that correct value.
     ? As `clearFormData` is reactive it is being changed from `null` to corrent value and thanks to reactivity it is also update in `useCalendarEventHandler` composition function and it is getting correct value in second time and can work w/o any issues.
    */

   const toast = useToast()

   const resources = ref([])

    const {
      roleLocal,
      resetTransferLocal,
      // UI
      onSubmit,
    } = useRoleModalHandler(toRefs(props), emit)

    
    const {
      refFormObserver,
      getValidationState,
      resetForm,
      clearForm,
    } = formValidation(resetTransferLocal, props.clearContactData)

    //Atualiza os dados do departamento
    const updatePermissionRole = (permissionData) => {
      store.dispatch('app-role/updatePermissionRole', { permissionData: permissionData, roleId: props.role.id })
        .then(() => {  
          getResources()

          toast({
            component: ToastificationContent,
            props: {
              title: 'Permissão atualizada com sucesso!',
              icon: 'CheckIcon',
              variant: 'success',
            },
          })
        })
    }

    const getResources = () => {
      store.dispatch('app-role/getResources', props.role.id)
        .then(response => {  
          resources.value = response.data; 
          
        })
    }

    getResources()


    return {
      roleLocal,
      resetTransferLocal,
      onSubmit,
      resources,

      updatePermissionRole,

      refFormObserver,
      getValidationState,
      resetForm,
      clearForm,
    }
  },
}

import 'vue-phone-number-input/dist/vue-phone-number-input.css';
</script>
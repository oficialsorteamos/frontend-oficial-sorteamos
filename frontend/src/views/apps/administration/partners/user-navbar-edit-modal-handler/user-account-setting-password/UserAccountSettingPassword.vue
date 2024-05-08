<template>
  <b-card>
    <!-- A função handleSubmit só deixa a o formulário ser submetido (só chama a função onSubmit) caso todos os campos do form satisfação os os pré-requisitos -->
    <validation-observer
      #default="{ handleSubmit }"
      ref="refFormObserver"
    >
      <!-- form -->
      <b-form
        enctype="multipart/form-data"
        @submit.prevent="handleSubmit(onSubmit)"
      >
        <input
          type="hidden"
          id="username"
          v-model="userLocal.username"
        />
        <b-row>
          <!-- old password -->
          <b-col md="6">
            <validation-provider
              #default="{ errors }"
              :name="$t('user.userAccountSettingPassword.oldPassword')"
              rules="required"
            >
              <b-form-group
                :label="$t('user.userAccountSettingPassword.oldPassword')"
                label-for="account-old-password"
              >
                <b-form-input
                  id="old-password"
                  v-model="userLocal.old_password"
                  :state="errors.length > 0 ? false : null"
                  name="old-password"
                  :placeholder="$t('user.userAccountSettingPassword.oldPasswordPlaceholder')"
                  trim
                  type="password"
                />
                <b-form-invalid-feedback :state="errors.length > 0 ? false:null">
                  {{ errors[0] }}
                </b-form-invalid-feedback>
              </b-form-group>
            </validation-provider>
          </b-col>
          <!--/ old password -->
        </b-row>
        <b-row>
          <!-- new password -->
          <b-col md="6">
            <b-form-group
              :label="$t('user.userAccountSettingPassword.password')"
              label-for="password"
            >
              <validation-provider
                #default="{ errors }"
                :name="$t('user.userAccountSettingPassword.password')"
                rules="required|password"
              >
                <b-input-group
                  class="input-group-merge"
                > 
                  <b-form-input
                    id="address-password"
                    v-model="userLocal.password"
                    :state="errors.length > 0 ? false : null"
                    class="form-control-merge"
                    trim
                    :placeholder="$t('user.userAccountSettingPassword.passwordPlaceholder')"
                    :type="passwordFieldType"
                  />
                </b-input-group>
                <small class="text-danger">{{ errors[0] }}</small>
              </validation-provider>
            </b-form-group>
          </b-col>
          <!--/ new password -->

          <!-- retype password -->
          <b-col md="6">
            <b-form-group
              :label="$t('user.userAccountSettingPassword.confirmPassword')"
              label-for="confirm-password"
            >
              <validation-provider
                #default="{ errors }"
                :name="$t('user.userAccountSettingPassword.confirmPassword')"
                rules="required|confirmed:Senha"
              >
                <b-input-group
                  class="input-group-merge"
                >
                  <b-form-input
                    id="confirm-password"
                    v-model="userLocal.confirm_password"
                    :state="errors.length > 0 ? false : null"
                    class="form-control-merge"
                    trim
                    :placeholder="$t('user.userAccountSettingPassword.confirmPasswordPlaceholder')"
                    :type="passwordFieldType"
                  />
                  <b-input-group-append is-text>
                    <feather-icon
                      class="cursor-pointer"
                      :icon="passwordToggleIcon"
                      @click="togglePasswordVisibility"
                    />
                  </b-input-group-append>
                </b-input-group>
                <small class="text-danger">{{ errors[0] }}</small>
              </validation-provider>
            </b-form-group>
          </b-col>
          <!-- retype password -->

          <!-- buttons -->
          <b-col cols="12">
            <b-button
              v-ripple.400="'rgba(255, 255, 255, 0.15)'"
              variant="primary"
              class="mt-1 mr-1"
              type="submit"
            >
              {{ $t('user.userAccountSettingPassword.update') }}
            </b-button>
          </b-col>
          <!--/ buttons -->
        </b-row>
      </b-form>
    </validation-observer>
  </b-card>
</template>

<script>
import {
  BButton, BForm, BFormGroup, BFormInput, BRow, BCol, BCard, BInputGroup, BInputGroupAppend, BFormInvalidFeedback,
} from 'bootstrap-vue'
import Ripple from 'vue-ripple-directive'
import { toRefs } from '@vue/composition-api'
import { ValidationProvider, ValidationObserver } from 'vee-validate'
import { required, email, url } from '@validations'
import formValidation from '@core/comp-functions/forms/form-validation'
import useUserAccountSettingPassword from './useUserAccountSettingPassword'
import { togglePasswordVisibility } from '@core/mixins/ui/forms'

export default {
  components: {
    BButton,
    BForm,
    BFormGroup,
    BFormInput,
    BRow,
    BCol,
    BCard,
    BInputGroup,
    BInputGroupAppend,
    BFormInvalidFeedback,

    // Form Validation
    ValidationProvider,
    ValidationObserver,
  },
  directives: {
    Ripple,
  },
  props: {
    clearUserData: {
      type: Function,
      required: true,
    },
    user: {
      type: Object,
      required: true,
    },
  },
  mixins: [togglePasswordVisibility],
  data() {
    return {
      
    }
  },
  computed: {
    passwordToggleIcon() {
      return this.passwordFieldType === 'password' ? 'EyeIcon' : 'EyeOffIcon'
    },
  },
  setup(props, {emit}) {

    const {
      userLocal,
      resetUserLocal,
      // UI
      onSubmit,
    } = useUserAccountSettingPassword(toRefs(props), emit)

    const {
      refFormObserver,
      getValidationState,
    } = formValidation(resetUserLocal, props.clearContactData)

    return {
      userLocal,
      resetUserLocal,
      // UI
      onSubmit,

      refFormObserver,
      getValidationState,
    }
  }
}
</script>

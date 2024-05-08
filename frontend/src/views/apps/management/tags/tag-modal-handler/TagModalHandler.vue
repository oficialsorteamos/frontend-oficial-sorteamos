<template>
  <div>
      <!-- A função handleSubmit só deixa a o formulário ser submetido (só chama a função onSubmit) caso todos os campos do form satisfação os os pré-requisitos -->
      <validation-observer
        #default="{ handleSubmit }"
        ref="refFormObserver"
      >
        <b-form
          enctype="multipart/form-data"
          @submit.prevent="handleSubmit(onSubmit)"
        > 
          <!-- Status -->
          <b-form-checkbox
            v-model="tagLocal.tag_status"
            name="check-button"
            class="mb-1"
            switch
            inline
            value="A"
            unchecked-value="I"
            v-if="tagLocal.id"
          >
            {{ $t('tag.tagModalHandler.status') }}
          </b-form-checkbox>

          <validation-provider
            #default="{ errors }"
            :name="$t('tag.tagModalHandler.typeTags')+'*'"
            rules="required"
          >
            <b-form-group
              :label="$t('tag.tagModalHandler.typeTags')+'*'"
              label-for="vue-select"
              :state="errors.length > 0 ? false:null"
            >
              <v-select
                id="vue-select"
                v-model="tagLocal.type_tag"
                :dir="$store.state.appConfig.isRTL ? 'rtl' : 'ltr'"
                :options="typesTag"
                :getOptionLabel="typesTag => typesTag.typ_name"
                transition=""
              />
              <b-form-invalid-feedback :state="errors.length > 0 ? false:null">
                {{ errors[0] }}
              </b-form-invalid-feedback>
            </b-form-group>
          </validation-provider>
          
          <!-- Name -->
          <validation-provider
            #default="validationContext"
            :name="$t('tag.tagModalHandler.name')"
            rules="required"
          >
            <b-form-group
              :label="$t('tag.tagModalHandler.name')+'*'"
              label-for="tag-name"
            >
              <b-form-input
                id="tag-name"
                v-model="tagLocal.tag_name"
                :state="getValidationState(validationContext)"
                trim
                :placeholder="$t('tag.tagModalHandler.namePlaceholder')"
                type="text"
                :maxlength="30"
                autocomplete="off"
              />

              <b-form-invalid-feedback>
                {{ validationContext.errors[0] }}
              </b-form-invalid-feedback>
            </b-form-group>
          </validation-provider>

          <!-- Description -->
          <validation-provider
            #default="validationContext"
            :name="$t('tag.tagModalHandler.description')"
            rules="required|min:10"
          >
            <b-form-group
              :label="$t('tag.tagModalHandler.description')"
              label-for="tag-description"
            >
              <b-form-input
                id="tag-description"
                v-model="tagLocal.tag_description"
                :state="getValidationState(validationContext)"
                trim
                :placeholder="$t('tag.tagModalHandler.descriptionPlaceholder')"
                type="text"
                :maxlength="120"
                autocomplete="off"
              />

              <b-form-invalid-feedback>
                {{ validationContext.errors[0] }}
              </b-form-invalid-feedback>
            </b-form-group>
          </validation-provider>

            <!-- Color -->
            <validation-provider
              #default="validationContext"
              :name="$t('tag.tagModalHandler.color')"
              rules="required"
            >
              <b-form-group
                :label="$t('tag.tagModalHandler.color')+'*'"
                label-for="tag-color"
              >
                <b-input-group>
                  <b-form-input 
                    id="tag-color"
                    v-model="tagLocal.tag_color"
                    :state="getValidationState(validationContext)"
                    trim
                    :placeholder="$t('tag.tagModalHandler.colorPlaceholder')"
                    type="text"
                    :maxlength="120"
                    autocomplete="off"
                  />
                  <b-input-group-append>
                    <b-button variant="outline-primary" style="padding: 2px 7px 2px 7px;" >
                      <verte 
                        v-model="tagLocal.tag_color"
                        picker="square" 
                        model="hex"
                        menuPosition="bottom"
                        value="#22b7ff"
                        :key="x"
                      >
                      </verte>
                    </b-button>
                  </b-input-group-append>
                  <b-form-invalid-feedback>
                    {{ validationContext.errors[0] }}
                  </b-form-invalid-feedback>
                </b-input-group>
              </b-form-group>
             </validation-provider>

          <!-- Form Actions -->
          <div class="d-flex mt-2 modal-footer">
            <b-button
              v-ripple.400="'rgba(255, 255, 255, 0.15)'"
              variant="primary"
              class="mr-2"
              type="submit"
            >
              {{ tagLocal.id ? $t('tag.tagModalHandler.update') : $t('tag.tagModalHandler.add') }}
            </b-button>
          </div>
        </b-form>
      </validation-observer>
  </div>
</template>

<script>
import {
  BButton, BModal, VBModal, BForm, BFormInput, BFormGroup, BFormInvalidFeedback, BFormDatepicker, BFormCheckbox,
  BRow, BCol, BInputGroup, BInputGroupAppend,
} from 'bootstrap-vue'
import axios from '@axios'
import vSelect from 'vue-select'
import Ripple from 'vue-ripple-directive'
import { toRefs } from '@vue/composition-api'
import { ValidationProvider, ValidationObserver } from 'vee-validate'
import useTagModalHandler from './useTagModalHandler'
import { required } from '@validations'
import formValidation from '@core/comp-functions/forms/form-validation'
import Verte from 'verte'
import 'verte/dist/verte.css'

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
    BRow,
    BCol,
    BInputGroup,
    BInputGroupAppend,

    // Form Validation
    ValidationProvider,
    ValidationObserver,
    
    //Color Picker
    Verte,

    
  },
  directives: {
    'b-modal': VBModal,
    Ripple,
  },
  props: {
    tag: {
      type: Object,
      required: true,
    },
    clearTagData: {
      type: Function,
      required: true,
    },
  },
  data() {
    return {
      x: 0,
      typesTag: [],
    }
  },
  created() {
    console.log('chamou') 
    //Traz os departamentos cadastrados
    axios
      .get('/api/management/tag/fetch-types-tag')
      .then(response => {
        console.log(response.data)
        this.typesTag = response.data.typesTag
      });
  },
  mounted() {
    setTimeout(() => {
        const x = 1
        this.x = x;
    }, 1500)
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

    const {
      tagLocal,
      resetTransferLocal,
      // UI
      onSubmit,
    } = useTagModalHandler(toRefs(props), emit)

    
    const {
      refFormObserver,
      getValidationState,
      resetForm,
      clearForm,
    } = formValidation(resetTransferLocal, props.clearContactData)

    return {
      tagLocal,
      resetTransferLocal,
      onSubmit,

      refFormObserver,
      getValidationState,
      resetForm,
      clearForm,
    }
  },
}

import 'vue-phone-number-input/dist/vue-phone-number-input.css';
</script>
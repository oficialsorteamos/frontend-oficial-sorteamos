<template>
  <b-card>
    <b-tabs>
      <b-tab active>
        <template #title>
          <feather-icon icon="ClipboardIcon" />
          <span>{{ $t('company.companyDetailsTabCard.plan') }}</span>
        </template>

        <b-card
          class="card-transaction"
          no-body
        >
          <b-card-header>
            <b-card-title></b-card-title>
            <feather-icon
              icon="EditIcon"
              size="18"
              class="cursor-pointer"
              @click="openModal('modal-edit-plan')"
            />
          </b-card-header>

          <b-card-body>
            <!-- Plan Value -->
            <b-row>
              <b-col
                cols="10"
                xl="10"
                lg="10"
                md="10"
              >
                <b-media 
                  no-body
                >
                  <b-media-aside>
                    <b-avatar
                      rounded
                      size="42"
                      variant="success"
                    >
                      <feather-icon
                        size="18"
                        icon="DollarSignIcon"
                      />
                    </b-avatar>
                  </b-media-aside>
                  <b-media-body>
                    <h6 class="transaction-title">
                      {{ $t('company.companyDetailsTabCard.planValue') }}
                    </h6>
                    <small>{{ $t('company.companyDetailsTabCard.monthlyPlanValue') }}</small>
                  </b-media-body>
                </b-media>
              </b-col>
              <b-col
                cols="2"
                xl="2"
                lg="2"
                md="2"
                class="mt-2"
                style="text-align: right"
              >
                <span
                  class="font-weight-bolder"
                >
                  R$ {{  plan.pla_value.toString().replace(".", ",") }}
                </span>
              </b-col>
            </b-row>
            <!-- Users -->
            <b-row
              class="mt-2"
            >
              <b-col
                cols="10"
                xl="10"
                lg="10"
                md="10"
              >
                <b-media 
                  no-body
                >
                  <b-media-aside>
                    <b-avatar
                      rounded
                      size="42"
                      variant="info"
                    >
                      <feather-icon
                        size="18"
                        icon="UserIcon"
                      />
                    </b-avatar>
                  </b-media-aside>
                  <b-media-body>
                    <h6 class="transaction-title">
                      {{ $t('company.companyDetailsTabCard.users') }}
                    </h6>
                    <small>{{ $t('company.companyDetailsTabCard.numberFreeUsers') }}</small>
                  </b-media-body>
                </b-media>
              </b-col>
              <b-col
                cols="2"
                xl="2"
                lg="2"
                md="2"
                class="mt-2"
                style="text-align: right"
              >
                <span
                  class="font-weight-bolder"
                >
                  {{ plan.pla_total_user }}
                </span>
              </b-col>
            </b-row>
            <!-- Official Channels -->
            <b-row
              class="mt-2"
            >
              <b-col
                cols="10"
                xl="10"
                lg="10"
                md="10"
              >
                <b-media 
                  no-body
                >
                  <b-media-aside>
                    <b-avatar
                      rounded
                      size="42"
                      variant="primary"
                    >
                      <feather-icon
                        size="18"
                        icon="PhoneIcon"
                      />
                    </b-avatar>
                  </b-media-aside>
                  <b-media-body>
                    <h6 class="transaction-title">
                      {{ $t('company.companyDetailsTabCard.officialChannels') }}
                    </h6>
                    <small>{{ $t('company.companyDetailsTabCard.numberFreeOfficialChannels') }}</small>
                  </b-media-body>
                </b-media>
              </b-col>
              <b-col
                cols="2"
                xl="2"
                lg="2"
                md="2"
                class="mt-2"
                style="text-align: right"
              >
                <span
                  class="font-weight-bolder"
                >
                  {{ plan.pla_total_official_channel }}
                </span>
              </b-col>
            </b-row>
            <!-- Unofficial Channels -->
            <b-row
              class="mt-2"
            >
              <b-col
                cols="10"
                xl="10"
                lg="10"
                md="10"
              >
                <b-media 
                  no-body
                >
                  <b-media-aside>
                    <b-avatar
                      rounded
                      size="42"
                      variant="dark"
                    >
                      <feather-icon
                        size="18"
                        icon="PhoneIcon"
                      />
                    </b-avatar>
                  </b-media-aside>
                  <b-media-body>
                    <h6 class="transaction-title">
                      {{ $t('company.companyDetailsTabCard.unofficialChannels') }}
                    </h6>
                    <small>{{ $t('company.companyDetailsTabCard.numberFreeUnofficialChannels') }}</small>
                  </b-media-body>
                </b-media>
              </b-col>
              <b-col
                cols="2"
                xl="2"
                lg="2"
                md="2"
                class="mt-2"
                style="text-align: right"
              >
                <span
                  class="font-weight-bolder"
                >
                  {{ plan.pla_total_unofficial_channel }}
                </span>
              </b-col>
            </b-row>
          </b-card-body>
        </b-card>
      </b-tab>
      <b-tab
      >
        <template #title>
          <feather-icon icon="DollarSignIcon" />
          <span>{{ $t('company.companyDetailsTabCard.charge') }}</span>
        </template>

        <b-card
          class="card-transaction"
          no-body
        >
          <b-card-body>
            <!-- Monthly Fee -->
            <b-row
              v-if="parametersCharge[1].type_parameter_id == 6"
            >
              <b-col
                cols="7"
                xl="7"
                lg="7"
                md="7"
              >
                <b-form-checkbox
                  v-model="parametersCharge[1].par_value"
                  name="check-button"
                  class="custom-control-success"
                  switch
                  inline
                  value="1"
                  unchecked-value="0"
                  @change="updateParametersCharge(parametersCharge)"
                >
                  {{ $t('company.companyDetailsTabCard.monthlyFee') }}
                </b-form-checkbox>
              </b-col>
              <b-col
                cols="5"
                xl="5"
                lg="5"
                md="5"
                v-if="parametersCharge[1].par_value == 1"
              >
                <b-form-checkbox
                  v-model="parametersCharge[1].par_proportional_charge"
                  name="check-button"
                  class="custom-control-success"
                  switch
                  inline
                  value="1"
                  unchecked-value="0"
                  @change="updateParametersCharge(parametersCharge)"
                >
                  {{ $t('company.companyDetailsTabCard.proportionalCharge') }}
                </b-form-checkbox>
              </b-col>
            </b-row>
            <!-- User Charge -->
            <b-row
              class="mt-2"
              v-if="parametersCharge[2].type_parameter_id == 7"
            >
              <b-col
                cols="7"
                xl="7"
                lg="7"
                md="7"
              >
                <b-form-checkbox
                  v-model="parametersCharge[2].par_value"
                  name="check-button"
                  class="custom-control-info"
                  switch
                  inline
                  value="1"
                  unchecked-value="0"
                  @change="updateParametersCharge(parametersCharge)"
                >
                  {{ $t('company.companyDetailsTabCard.chargeAdditionalUser') }}
                </b-form-checkbox>
              </b-col>
              <b-col
                cols="5"
                xl="5"
                lg="5"
                md="5"
                v-if="parametersCharge[2].par_value == 1"
              >
                <b-form-checkbox
                  v-model="parametersCharge[2].par_proportional_charge"
                  name="check-button"
                  class="custom-control-info"
                  switch
                  inline
                  value="1"
                  unchecked-value="0"
                  @change="updateParametersCharge(parametersCharge)"
                >
                  {{ $t('company.companyDetailsTabCard.proportionalCharge') }}
                </b-form-checkbox>
              </b-col>
            </b-row>
            <!-- Official Channel Charge -->
            <b-row
              class="mt-2"
              v-if="parametersCharge[3].type_parameter_id == 8"
            >
              <b-col
                cols="7"
                xl="7"
                lg="7"
                md="7"
              >
                <b-form-checkbox
                  v-model="parametersCharge[3].par_value"
                  name="check-button"
                  class="custom-control-primary"
                  switch
                  inline
                  value="1"
                  unchecked-value="0"
                  @change="updateParametersCharge(parametersCharge)"
                >
                  {{ $t('company.companyDetailsTabCard.chargeAdditionalOfficialChannel') }}
                </b-form-checkbox>
              </b-col>
              <b-col
                cols="5"
                xl="5"
                lg="5"
                md="5"
                v-if="parametersCharge[3].par_value == 1"
              >
                <b-form-checkbox
                  v-model="parametersCharge[3].par_proportional_charge"
                  name="check-button"
                  class="custom-control-primary"
                  switch
                  inline
                  value="1"
                  unchecked-value="0"
                  @change="updateParametersCharge(parametersCharge)"
                >
                  {{ $t('company.companyDetailsTabCard.proportionalCharge') }}
                </b-form-checkbox>
              </b-col>
            </b-row>
            <!-- Unofficial Channel Charge -->
            <b-row
              class="mt-2"
              v-if="parametersCharge[4].type_parameter_id == 9"
            >
              <b-col
                cols="7"
                xl="7"
                lg="7"
                md="7"
              >
                <b-form-checkbox
                  v-model="parametersCharge[4].par_value"
                  name="check-button"
                  class="custom-control-dark"
                  switch
                  inline
                  value="1"
                  unchecked-value="0"
                  @change="updateParametersCharge(parametersCharge)"
                >
                  {{ $t('company.companyDetailsTabCard.chargeAdditionalUnofficialChannel') }}
                </b-form-checkbox>
              </b-col>
              <b-col
                cols="5"
                xl="5"
                lg="5"
                md="5"
                v-if="parametersCharge[4].par_value == 1"
              >
                <b-form-checkbox
                  v-model="parametersCharge[4].par_proportional_charge"
                  name="check-button"
                  class="custom-control-dark"
                  switch
                  inline
                  value="1"
                  unchecked-value="0"
                  @change="updateParametersCharge(parametersCharge)"
                >
                  {{ $t('company.companyDetailsTabCard.proportionalCharge') }}
                </b-form-checkbox>
              </b-col>
            </b-row>
            <b-row
              class="mt-2"
              v-if="parametersCharge[0].type_parameter_id == 5"
            >
              <b-col
                cols="7"
                xl="7"
                lg="7"
                md="7"
              >
                <b-form-checkbox
                  v-model="parametersCharge[0].par_value"
                  name="check-button"
                  class="custom-control-warning"
                  switch
                  inline
                  value="1"
                  unchecked-value="0"
                  @change="updateParametersCharge(parametersCharge)"
                >
                  {{ $t('company.companyDetailsTabCard.chargeSendingCampaignMessage') }}
                </b-form-checkbox>
              </b-col>
            </b-row>
            <!-- Se for uma cobrança de envio de SMS -->
            <b-row
              class="mt-2"
              v-if="parametersCharge[5].type_parameter_id == 10"
            >
              <b-col
                cols="7"
                xl="7"
                lg="7"
                md="7"
              >
                <b-form-checkbox
                  v-model="parametersCharge[5].par_value"
                  name="check-button"
                  class="custom-control-warning"
                  switch
                  inline
                  value="1"
                  unchecked-value="0"
                  @change="updateParametersCharge(parametersCharge)"
                >
                  {{ $t('company.companyDetailsTabCard.chargeSendingCampaignSmsMessage') }}
                </b-form-checkbox>
              </b-col>
            </b-row>
            <!-- Se for uma cobrança de retorno do contato por SMS -->  
            <b-row
              class="mt-2"
              v-if="parametersCharge[6].type_parameter_id == 11"
            >
              <b-col
                cols="7"
                xl="7"
                lg="7"
                md="7"
              >
                <b-form-checkbox
                  v-model="parametersCharge[6].par_value"
                  name="check-button"
                  class="custom-control-warning"
                  switch
                  inline
                  value="1"
                  unchecked-value="0"
                  @change="updateParametersCharge(parametersCharge)"
                >
                  {{ $t('company.companyDetailsTabCard.chargeReturnCampaignSmsMessage') }}
                </b-form-checkbox>
              </b-col>
            </b-row>
            <b-row
              class="mt-2"
              v-if="parametersCharge[7].type_parameter_id == 12"
            >
              <b-col
                cols="7"
                xl="7"
                lg="7"
                md="7"
              >
                <b-form-checkbox
                  v-model="parametersCharge[7].par_value"
                  name="check-button"
                  class="custom-control-warning"
                  switch
                  inline
                  value="1"
                  unchecked-value="0"
                  @change="updateParametersCharge(parametersCharge)"
                >
                  {{ $t('company.companyDetailsTabCard.chargeCallWhatsappCampaignSmsMessage') }}
                </b-form-checkbox>
              </b-col>
            </b-row>
          </b-card-body>
        </b-card>
      </b-tab>
      <b-tab
      >
        <template #title>
          <feather-icon icon="SettingsIcon" />
          <span>{{ $t('company.companyDetailsTabCard.general') }}</span>
        </template>

        <b-card
          class="card-transaction"
          no-body
        >
          <b-card-header>
            <b-card-title></b-card-title>
            <feather-icon
              icon="EditIcon"
              size="18"
              class="cursor-pointer"
              @click="openModal('modal-edit-general')"
            />
          </b-card-header>

          <b-card-body>
            <!-- Plan Value -->
            <b-row>
              <b-col
                cols="10"
                xl="10"
                lg="10"
                md="10"
              >
                <b-media 
                  no-body
                >
                  <b-media-aside>
                    <b-avatar
                      rounded
                      size="42"
                      variant="primary"
                    >
                      <feather-icon
                        size="18"
                        icon="CalendarIcon"
                      />
                    </b-avatar>
                  </b-media-aside>
                  <b-media-body>
                    <h6 class="transaction-title">
                      {{ $t('company.companyDetailsTabCard.startOperation') }}
                    </h6>
                    <small>{{ $t('company.companyDetailsTabCard.dateCompanyStartedUsingSystem') }}</small>
                  </b-media-body>
                </b-media>
              </b-col>
              <b-col
                cols="2"
                xl="2"
                lg="2"
                md="2"
                class="mt-2"
                style="text-align: right"
              >
                <span
                  class="font-weight-bolder"
                  v-if="parametersGeneral[0]"
                >
                  {{  parametersGeneral[0].par_value }}
                </span>
              </b-col>
            </b-row>
          </b-card-body>
        </b-card>
      </b-tab>
      <b-tab
      >
        <template #title>
          <feather-icon icon="HomeIcon" />
          <span>{{ $t('company.companyDetailsTabCard.whiteLabel') }}</span>
        </template>

        <b-card
          class="card-transaction"
          no-body
        >
          <b-card-header>
            <b-card-title></b-card-title>
            <feather-icon
              icon="EditIcon"
              size="18"
              class="cursor-pointer"
              @click="openModal('modal-edit-white-label')"
            />
          </b-card-header>

          <b-card-body>
            <!-- White Label -->
            <b-row
              class="mb-2"
            >
              <b-col
                cols="6"
                xl="6"
                lg="6"
                md="6"
              >
                <b-media 
                  no-body
                >
                  <b-media-aside>
                    <b-avatar
                      rounded
                      size="42"
                      variant="primary"
                    >
                      <feather-icon
                        size="18"
                        icon="BriefcaseIcon"
                      />
                    </b-avatar>
                  </b-media-aside>
                  <b-media-body>
                    <h6 class="transaction-title">
                      {{ $t('company.companyDetailsTabCard.whiteLabelName') }}
                    </h6>
                    <small>{{ $t('company.companyDetailsTabCard.whiteLabelNameDescription') }}</small>
                  </b-media-body>
                </b-media>
              </b-col>
              <b-col
                cols="6"
                xl="6"
                lg="6"
                md="6"
                class="mt-2"
                style="text-align: right"
              >
                <span
                  class="font-weight-bolder"
                >
                  {{  whiteLabel.whi_name }}
                </span>
              </b-col>
            </b-row>
            <b-row
              class="mb-2"
            >
              <b-col
                cols="6"
                xl="6"
                lg="6"
                md="6"
              >
                <b-media 
                  no-body
                >
                  <b-media-aside>
                    <b-avatar
                      rounded
                      size="42"
                      variant="dark"
                    >
                      <feather-icon
                        size="18"
                        icon="FileTextIcon"
                      />
                    </b-avatar>
                  </b-media-aside>
                  <b-media-body>
                    <h6 class="transaction-title">
                      {{ $t('company.companyDetailsTabCard.whiteLabelDocument') }}
                    </h6>
                    <small>{{ $t('company.companyDetailsTabCard.whiteLabelDocumentDescription') }}</small>
                  </b-media-body>
                </b-media>
              </b-col>
              <b-col
                cols="6"
                xl="6"
                lg="6"
                md="6"
                class="mt-2"
                style="text-align: right"
              >
                <span
                  class="font-weight-bolder"
                >
                  {{  whiteLabel.whi_document_number }}
                </span>
              </b-col>
            </b-row>
            <b-row
              class="mb-2"
            >
              <b-col
                cols="6"
                xl="6"
                lg="6"
                md="6"
              >
                <b-media 
                  no-body
                >
                  <b-media-aside>
                    <b-avatar
                      rounded
                      size="42"
                      variant="success"
                    >
                      <feather-icon
                        size="18"
                        icon="LinkIcon"
                      />
                    </b-avatar>
                  </b-media-aside>
                  <b-media-body>
                    <h6 class="transaction-title">
                      {{ $t('company.companyDetailsTabCard.whiteLabelUrl') }}
                    </h6>
                    <small>{{ $t('company.companyDetailsTabCard.whiteLabelUrlDescription') }}</small>
                  </b-media-body>
                </b-media>
              </b-col>
              <b-col
                cols="6"
                xl="6"
                lg="6"
                md="6"
                class="mt-2"
                style="text-align: right"
              >
                <span
                  class="font-weight-bolder"
                >
                  <a :href="whiteLabel.whi_url" target="_blank" rel="noopener noreferrer">{{  whiteLabel.whi_url }}</a>
                </span>
              </b-col>
            </b-row>
          </b-card-body>
        </b-card>
      </b-tab>
      <b-tab
      >
        <template #title>
          <feather-icon icon="PenToolIcon" />
          <span>{{ $t('company.companyDetailsTabCard.customization') }}</span>
        </template>

        <b-card
          class="card-transaction"
          no-body
        >
          <b-card-header>
            <b-card-title></b-card-title>
            <feather-icon
              icon="EditIcon"
              size="18"
              class="cursor-pointer"
              @click="openModal('modal-edit-customization')"
            />
          </b-card-header>

          <b-card-body>
            <b-row>
              <b-col
                cols="6"
                xl="6"
                lg="6"
                md="6"
                class="mt-2"
              >
                <b-media 
                  no-body
                >
                  <b-media-aside>
                    <b-avatar
                      rounded
                      size="42"
                      variant="dark"
                    >
                      <feather-icon
                        size="18"
                        icon="ImageIcon"
                      />
                    </b-avatar>
                  </b-media-aside>
                  <b-media-body>
                    <h6 class="transaction-title">
                      {{ $t('company.companyDetailsTabCard.customizationLogo') }}
                    </h6>
                    <small>{{ $t('company.companyDetailsTabCard.customizationLogoDescription') }}</small>
                  </b-media-body>
                </b-media>
              </b-col>
              <b-col
                cols="6"
                xl="6"
                lg="6"
                md="6"
                class="mt-2"
                style="text-align: right"
              >
                <span
                  class="brand-logo"
                >
                  <b-img
                    :src="'/images/logo/logo.png'"
                    alt="logo"
                    width="110px"
                  />
                </span>
              </b-col>
            </b-row>
            <b-row>
              <b-col
                cols="6"
                xl="6"
                lg="6"
                md="6"
                class="mt-2"
              >
                <b-media 
                  no-body
                >
                  <b-media-aside>
                    <b-avatar
                      rounded
                      size="42"
                      variant="primary"
                    >
                      <feather-icon
                        size="18"
                        icon="ImageIcon"
                      />
                    </b-avatar>
                  </b-media-aside>
                  <b-media-body>
                    <h6 class="transaction-title">
                      {{ $t('company.companyDetailsTabCard.customizationFavicon') }}
                    </h6>
                    <small>{{ $t('company.companyDetailsTabCard.customizationFaviconDescription') }}</small>
                  </b-media-body>
                </b-media>
              </b-col>
              <b-col
                cols="6"
                xl="6"
                lg="6"
                md="6"
                class="mt-2"
                style="text-align: right"
              >
                <span
                  class="brand-logo"
                >
                  <b-img
                    :src="'/images/logo/favicon.png'"
                    alt="logo"
                    width="40px"
                  />
                </span>
              </b-col>
            </b-row>
          </b-card-body>
        </b-card>
      </b-tab>
    </b-tabs>

    <!-- Modal para edição dos dados do plano -->
    <b-modal
      :id="'modal-edit-plan'"
      :title="$t('company.companyDetailsTabCard.editPlan')"
      hide-footer
      size="sm"
    >
      <company-modal-edit-plan-handler
        :plan="plan"
        :clear-contact-data="clearParametersData"
        @update-plan="updatePlan"
        @hide-modal="hideModal"
      />
    </b-modal>
    <!-- Modal para edição dos dados gerais da empresa -->
    <b-modal
      :id="'modal-edit-general'"
      :title="$t('company.companyDetailsTabCard.editGeneralData')"
      hide-footer
      size="sm"
    >
      <company-modal-edit-general-handler
        :parameters-general="parametersGeneral"
        :clear-contact-data="clearParametersData"
        @update-general="updateParametersGeneral"
        @hide-modal="hideModal"
      />
    </b-modal>
    <!-- Modal para edição dos dados do White Label -->
    <b-modal
      :id="'modal-edit-white-label'"
      :title="$t('company.companyDetailsTabCard.editPlan')"
      hide-footer
      size="sm"
    >
      <company-modal-edit-white-label-handler
        :white-label="whiteLabel"
        :clear-contact-data="clearParametersData"
        @update-white-label="updateWhiteLabel"
        @hide-modal="hideModal"
      />
    </b-modal>
    <b-modal
      :id="'modal-edit-customization'"
      :title="$t('company.companyDetailsTabCard.editPlan')"
      hide-footer
      size="lg"
    >
      <company-modal-edit-customization-handler
        :white-label="whiteLabel"
        :clear-contact-data="clearParametersData"
        @upload-logo="handleLogoUpload"
        @upload-favicon="handleFaviconUpload"
        @update-customization="updateCustomization"
        @hide-modal="hideModal"
      />
    </b-modal>
  </b-card>
</template>

<script>
import {
  ref
} from '@vue/composition-api'
import store from '@/store'
import { BTabs, BTab, BCardText, BCard, BCardHeader, BCardTitle, BCardBody, BRow, BCol, BImg,
  BMediaBody, BMedia, BMediaAside, BAvatar, BFormCheckbox, BFormGroup, BFormInput, BFormInvalidFeedback, } from 'bootstrap-vue'
import CompanyModalEditPlanHandler from './company-modal-edit-plan-handler/CompanyModalEditPlanHandler.vue'
import CompanyModalEditWhiteLabelHandler from './company-modal-edit-white-label-handler/CompanyModalEditWhiteLabelHandler.vue'
import CompanyModalEditCustomizationHandler from './company-modal-edit-customization-handler/CompanyModalEditCustomizationHandler.vue'
import CompanyModalEditGeneralHandler from './company-modal-edit-general-handler/CompanyModalEditGeneralHandler.vue'
import { ValidationProvider, ValidationObserver } from 'vee-validate'
import { required, email, url } from '@validations'
import formValidation from '@core/comp-functions/forms/form-validation'
import { VueMaskDirective } from 'v-mask'
// Notification
import { useToast } from 'vue-toastification/composition'
import ToastificationContent from '@core/components/toastification/ToastificationContent.vue'
import Vue from 'vue'
Vue.directive('mask', VueMaskDirective)


export default {
  components: {
    BCardText,
    BTabs,
    BTab,
    BCard,
    BCardHeader,
    BCardTitle,
    BCardBody,
    BMediaBody, 
    BMedia, 
    BMediaAside, 
    BAvatar,
    BFormCheckbox,
    BRow,
    BCol,
    BImg,
    BFormGroup,
    BFormInput,
    BFormInvalidFeedback,

    ValidationProvider,
    ValidationObserver,

    CompanyModalEditPlanHandler,
    CompanyModalEditWhiteLabelHandler,
    CompanyModalEditCustomizationHandler,
    CompanyModalEditGeneralHandler,

    //Máscara
    VueMaskDirective,
  },
  props: {
    plan: {
      type: Object,
      required: true,
    },
    whiteLabel: {
      type: Object,
      required: true,
    },
    parametersCharge: {
      type: Array,
      required: true,
    },
    parametersGeneral: {
      type: Array,
      required: true,
    },
  },
  methods: {
    hideModal(modalName) {
      //Fecha o Modal
      this.$root.$emit('bv::hide::modal', modalName, '#btnShow')
    },
    openModal(modalName) {
      //Abre o Modal
      this.$root.$emit('bv::show::modal', modalName, '#btnShow')
    },
  },
  setup(_, {emit}) {
    const toast = useToast()

    const blankParameters = {
      cha_name: '',
      cha_company_email: '',
    }
    const parametersData = ref(JSON.parse(JSON.stringify(blankParameters)))
    //Limpa os dados do popup
    const clearParametersData = () => {
      parametersData.value = JSON.parse(JSON.stringify(blankParameters))
    }

    //Atualiza os dados do plano
    const updatePlan = planData => {
      store.dispatch('app-company/updatePlan', { planData: planData })
        .then(response => {  
          emit('set-plan', response.data.plan)
          toast({
            component: ToastificationContent,
            props: {
              title: 'Plano atualizado com sucesso!',
              icon: 'CheckIcon',
              variant: 'success',
            },
          })
        })
    }

    //Atualiza os dados do plano
    const updateWhiteLabel = whiteLabelData => {
      store.dispatch('app-company/updateWhiteLabel',  whiteLabelData)
        .then(response => {  
          if(response.data.success) {
            emit('set-white-label', response.data.whiteLabel)
            toast({
              component: ToastificationContent,
              props: {
                title: 'Configurações do White Label atualizadas com sucesso!',
                icon: 'CheckIcon',
                variant: 'success',
              },
            })  
          }
          else {
            toast({
              component: ToastificationContent,
              props: {
                title: 'Não existe um parceiro com o CNPJ/CPF informado!',
                icon: 'CheckIcon',
                variant: 'danger',
              },
            })
          }
        })
    }

    const logoFile = ref('')
    const faviconFile = ref('')

    const handleLogoUpload = (fileData) => {
      logoFile.value = fileData
    }

    const handleFaviconUpload = (fileData) => {
      faviconFile.value = fileData
    }

    //Atualiza os dados do plano
    const updateCustomization = customizationData => {
      
      const formData = new FormData()
      formData.append('name', 'file.jpg')
      formData.append('logoFile', logoFile.value)
      formData.append('faviconFile', faviconFile.value)
      //formData.append('contractData', JSON.stringify(contractData))
      
      const config = {
          headers: {
            'content-type': 'multipart/form-data'
          }
      }
      
      store.dispatch('app-company/updateCustomization', formData, config)
        .then(response => {
          //Se houver alguma mensagem de erro
          if(response.data.errorMessage) {
            toast({
              component: ToastificationContent,
              props: {
                title: response.data.errorMessage,
                icon: 'AlertTriangleIcon',
                variant: 'danger',
              },
            },
            {
              timeout: false,
            })
          }
          else {
            toast({
              component: ToastificationContent,
              props: {
                title: 'Customização atualizada com sucesso!',
                icon: 'CheckIcon',
                variant: 'success',
              },
            })
          }   
        })
    }

    //Atualiza os dados dos parâmetros de cobrança
    const updateParametersCharge = parametersData => {
      store.dispatch('app-company/updateParametersCharge', { parametersData: parametersData })
        .then(response => {  
          //emit('set-plan', response.data.plan)
          toast({
            component: ToastificationContent,
            props: {
              title: 'Parâmetro atualizado com sucesso!',
              icon: 'CheckIcon',
              variant: 'success',
            },
          })
        })
    }

    //Atualiza os dados do plano
    const updateParametersGeneral = generalData => {
      store.dispatch('app-company/updateParametersGeneral', { generalData: generalData })
        .then(response => {  
          emit('set-general-data', response.data.generalData)
          toast({
            component: ToastificationContent,
            props: {
              title: 'Dados gerais atualizados com sucesso!',
              icon: 'CheckIcon',
              variant: 'success',
            },
          })
        })
    }

    const {
      refFormObserver,
      getValidationState,
      resetForm,
      clearForm,
    } = formValidation(null, clearParametersData)

    return {
      updatePlan,
      updateWhiteLabel,
      updateCustomization,
      updateParametersCharge,
      updateParametersGeneral,
      clearParametersData,
      handleLogoUpload,
      handleFaviconUpload,

      refFormObserver,
      getValidationState,
      resetForm,
      clearForm,
    }
  }
}
</script>
<template>
  <section class="invoice-preview-wrapper">

    <!-- Alert: No item found -->
    <b-alert
      variant="danger"
      :show="invoiceData === undefined"
    >
      <h4 class="alert-heading">
        Error fetching invoice data
      </h4>
      <div class="alert-body">
        No invoice found with this invoice id. Check
        <b-link
          class="alert-link"
          :to="{ name: 'apps-invoice-list'}"
        >
          Invoice List
        </b-link>
        for other invoices.
      </div>
    </b-alert>

    <b-row
      v-if="invoiceData"
      class="invoice-preview"
      style="margin-left: 20%"
    >

      <!-- Col: Left (Invoice Container) -->
      <b-col
        cols="12"
        xl="9"
        md="8"
      >
        <b-card
          no-body
          class="invoice-preview-card"
        >
          <!-- Header -->
          <b-card-body class="invoice-padding pb-0">

            <div class="d-flex justify-content-between flex-md-row flex-column invoice-spacing mt-0">

              <!-- Header: Left Content -->
              <div>
                <div class="logo-wrapper">
                  <b-link class="brand-logo">
                    <b-img
                      :src="require('@/assets/images/logo/logo-devsky.png')"
                      alt="logo"
                      width="180"
                    />
                  </b-link>
                </div>
                <p class="card-text mb-25">
                  Devsky Soluções em Tecnologia e
                </p>
                <p class="card-text mb-25">
                  Consultoria em Informática
                </p>
                <p class="card-text mb-0">
                  CNPJ: 47.232.185/0001-23
                </p>
              </div>

              <!-- Header: Right Content -->
              <div class="mt-md-0 mt-2">
                <h4 class="invoice-title">
                  {{ $t('invoice.invoiceDetails.invoice') }}:
                  <span class="invoice-number">{{ invoiceData.par_month_year }}</span>
                </h4>
                <div class="invoice-date-wrapper">
                  <p class="invoice-date-title">
                    {{ $t('invoice.invoiceDetails.opening') }}:
                  </p>
                  <p class="invoice-date">
                    {{ formatDateOnlyNumber(invoiceData.par_opening) }}
                  </p>
                </div>
                <div class="invoice-date-wrapper">
                  <p class="invoice-date-title">
                    {{ $t('invoice.invoiceDetails.closing') }}:
                  </p>
                  <p class="invoice-date">
                    {{ formatDateOnlyNumber(invoiceData.par_closing) }}
                  </p>
                </div>
                <div class="invoice-date-wrapper">
                  <p class="invoice-date-title">
                    <strong>{{ $t('invoice.invoiceDetails.due') }}:</strong>
                  </p>
                  <p class="invoice-date">
                    {{ formatDateOnlyNumber(invoiceData.par_due) }}
                  </p>
                </div>
              </div>
            </div>
          </b-card-body>

          <!-- Spacer -->
          <hr class="invoice-spacing">

          <!-- Invoice Description: Table -->
          <b-table-lite
            responsive
            :items="invoiceData.invoice_fees"
            :fields="tableColumns"
          >
            <template #cell(type_fee_id)="data">
              <!-- Se a taxa for a mensalidade -->
              <span
                v-if="data.item.type_fee_id == 1"
              >
                <b-card-text class="font-weight-bold mb-25">
                  {{ $t('invoice.invoiceDetails.monthlyFixedFee') }}
                </b-card-text>
                <b-card-text class="text-nowrap">
                  {{ $t('invoice.invoiceDetails.monthlyFixedFeeDescription') }}
                </b-card-text>
              </span>
              <!-- Se a taxa for o usuário -->
              <span
                v-if="data.item.type_fee_id == 2"
              >
                <b-card-text class="font-weight-bold mb-25">
                  {{ $t('invoice.invoiceDetails.feePerUser') }}
                </b-card-text>
                <b-card-text class="text-nowrap">
                  {{ $t('invoice.invoiceDetails.monthlyFeeChargedPerUser') }}
                </b-card-text>
              </span>
              <!-- Se a taxa for o canal Oficial -->
              <span
                v-if="data.item.type_fee_id == 3"
              >
                <b-card-text class="font-weight-bold mb-25">
                  {{ $t('invoice.invoiceDetails.feePerOfficialChannel') }}
                </b-card-text>
                <b-card-text class="text-nowrap">
                  {{ $t('invoice.invoiceDetails.monthlyFeeChargedPerOfficialChannel') }}
                </b-card-text>
              </span>
              <!-- Se a taxa for o canal -->
              <span
                v-if="data.item.type_fee_id == 4"
              >
                <b-card-text class="font-weight-bold mb-25">
                  {{ $t('invoice.invoiceDetails.feePerUnofficialChannel') }}
                </b-card-text>
                <b-card-text class="text-nowrap">
                  {{ $t('invoice.invoiceDetails.monthlyFeeChargedPerUnofficialChannel') }}
                </b-card-text>
              </span>
            </template>

            <template #cell(inv_total_value_fee)="data">
              R$ {{ data.item.par_total_value_fee.replace('.', ',') }}
            </template>
          </b-table-lite>

          <!-- Invoice Description: Total -->
          <b-card-body class="invoice-padding pb-0">
            <b-row>

              <!-- Col: Sales Persion -->
              <b-col
                cols="12"
                md="6"
                class="mt-md-0 mt-3"
                order="2"
                order-md="1"
              >
              <!--
                <b-card-text class="mb-0">
                  <span class="font-weight-bold">Salesperson:</span>
                  <span class="ml-75">Alfie Solomons</span>
                </b-card-text>
              -->
              </b-col>

              <!-- Col: Total -->
              <b-col
                cols="12"
                md="6"
                class="mt-md-6 d-flex justify-content-end"
                order="1"
                order-md="2"
              >
                <div class="invoice-total-wrapper">
                  <div 
                    class="invoice-total-item"
                    v-if="invoiceData.total_monthly_value"
                  >
                    <p class="invoice-total-title">
                      {{ $t('invoice.invoiceDetails.monthlyFixedFee') }}
                    </p>
                    <p class="invoice-total-amount">
                      R$ {{ invoiceData.total_monthly_value.replace('.', ',') }}
                    </p>
                  </div>
                  <div 
                    class="invoice-total-item"
                    v-if="invoiceData.total_user_value"
                  >
                    <p class="invoice-total-title">
                      {{ $t('invoice.invoiceDetails.totalFeeUsers') }}
                    </p>
                    <p class="invoice-total-amount">
                      R$ {{ invoiceData.total_user_value.replace('.', ',') }}
                    </p>
                  </div>
                  <div 
                    class="invoice-total-item"
                    v-if="invoiceData.total_official_channel_value"
                  >
                    <p class="invoice-total-title">
                      {{ $t('invoice.invoiceDetails.totalFeeOfficialsChannels') }}
                    </p>
                    <p class="invoice-total-amount">
                      R$ {{ invoiceData.total_official_channel_value.replace('.', ',') }}
                    </p>
                  </div>
                  <div 
                    class="invoice-total-item"
                    v-if="invoiceData.total_unofficial_channel_value"
                  >
                    <p class="invoice-total-title">
                      {{ $t('invoice.invoiceDetails.totalFeeUnofficialsChannels') }}
                    </p>
                    <p class="invoice-total-amount">
                      R$ {{ invoiceData.total_unofficial_channel_value.replace('.', ',') }}
                    </p>
                  </div>
                  <hr class="my-50">
                  <div class="invoice-total-item">
                    <p class="invoice-total-title">
                      Total:
                    </p>
                    <p class="invoice-total-amount">
                      R$ {{ invoiceData.total_invoice_value.replace('.', ',') }}
                    </p>
                  </div>
                </div>
              </b-col>
            </b-row>
          </b-card-body>

          <!-- Spacer -->
          <hr class="invoice-spacing">

          <!-- Note -->
          <b-card-body class="invoice-padding pt-0">
            <span class="font-weight-bold">{{ $t('invoice.invoiceDetails.caution') }}: </span>
            <span>{{ $t('invoice.invoiceDetails.cautionDescription') }}</span>
          </b-card-body>
        </b-card>
      </b-col>
    </b-row>
  </section>
</template>

<script>
import { ref, onUnmounted } from '@vue/composition-api'
import {
  BRow, BCol, BCard, BCardBody, BTableLite, BCardText, BButton, BAlert, BLink, VBToggle, BImg,
} from 'bootstrap-vue'
import Ripple from 'vue-ripple-directive'
import { formatDateOnlyNumber } from '@core/utils/filter'

export default {
  directives: {
    Ripple,
    'b-toggle': VBToggle,
  },
  components: {
    BRow,
    BCol,
    BCard,
    BCardBody,
    BTableLite,
    BCardText,
    BButton,
    BAlert,
    BLink,
    BImg,

  },
  data() {
    return {
      tableColumns: [
        { key: 'type_fee_id', label: 'Tipo de Taxa'},
        { key: 'inv_total_value_fee', label: 'Valor'},
      ]
    }
  },
  props: {
    invoiceData: {
      type: Object,
      required: true,
    },
  },
  setup() {

    const printInvoice = () => {
      window.print()
    }

    return {
      printInvoice,
      formatDateOnlyNumber,
    }
  },
}
</script>

<style lang="scss" scoped>
@import "~@core/scss/base/pages/app-invoice.scss";
</style>

<style lang="scss">
@media print {

  // Global Styles
  body {
    background-color: transparent !important;
  }
  nav.header-navbar {
    display: none;
  }
  .main-menu {
    display: none;
  }
  .header-navbar-shadow {
    display: none !important;
  }
  .content.app-content {
    margin-left: 0;
    padding-top: 2rem !important;
  }
  footer.footer {
    display: none;
  }
  .card {
    background-color: transparent;
    box-shadow: none;
  }
  .customizer-toggle {
    display: none !important;
  }

  // Invoice Specific Styles
  .invoice-preview-wrapper {
    .row.invoice-preview {
      .col-md-8 {
        max-width: 100%;
        flex-grow: 1;
      }

      .invoice-preview-card {
        .card-body:nth-of-type(2) {
          .row {
              > .col-12 {
              max-width: 50% !important;
            }

            .col-12:nth-child(2) {
              display: flex;
              align-items: flex-start;
              justify-content: flex-end;
              margin-top: 0 !important;
            }
          }
        }
      }
    }

    // Action Right Col
    .invoice-actions {
      display: none;
    }
  }
}
</style>

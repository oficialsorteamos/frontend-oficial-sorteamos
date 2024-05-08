import { ref, computed, watch } from '@vue/composition-api'
import { formatDateOnlyNumber } from '@core/utils/filter'

export default function useCampaignModalNewMessageHandler(props, emit) {
  // ------------------------------------------------
  // eventLocal
  // ------------------------------------------------
  const paymentOrderLocal = ref(JSON.parse(JSON.stringify(props.paymentOrder.value)))
  
  const resetTransferLocal = () => {
    paymentOrderLocal.value = JSON.parse(JSON.stringify(props.paymentOrder.value))
  }
  watch(props.paymentOrder, () => {
    resetTransferLocal()
  })

  const onSubmit = () => {
    const paymentOrderData = JSON.parse(JSON.stringify(paymentOrderLocal))
    if (props.paymentOrder.value.id) emit('upload-payment-receipt', paymentOrderData.value)
    
    emit('hide-modal', 'modal-upload-payment-receipt-'+paymentOrderData.value.contractId)
  }

  return {
    paymentOrderLocal,

    onSubmit,
  }
}

import { ref, computed, watch } from '@vue/composition-api'
import { formatDateOnlyNumber } from '@core/utils/filter'

export default function useCampaignModalNewMessageHandler(props, emit) {
  // ------------------------------------------------
  // eventLocal
  // ------------------------------------------------
  const contractLocal = ref(JSON.parse(JSON.stringify(props.contract.value)))
  
  contractLocal.value.com_dt_start = formatDateOnlyNumber(contractLocal.value.com_dt_start)
  contractLocal.value.com_dt_end = formatDateOnlyNumber(contractLocal.value.com_dt_end)
  
  const resetTransferLocal = () => {
    contractLocal.value = JSON.parse(JSON.stringify(props.contract.value))
  }
  watch(props.contract, () => {
    resetTransferLocal()
  })

  const onSubmit = () => {
    const contractData = JSON.parse(JSON.stringify(contractLocal))
    if (props.contract.value.id) emit('update-contract', contractData.value)
    else emit('add-contract', contractData.value)
    
    //Fecha o modal
    emit('hide-modal', 'modal-new-contract')
    emit('hide-modal', 'modal-new-contract-'+contractData.value.contractId)
  }

  return {
    contractLocal,

    onSubmit,
  }
}

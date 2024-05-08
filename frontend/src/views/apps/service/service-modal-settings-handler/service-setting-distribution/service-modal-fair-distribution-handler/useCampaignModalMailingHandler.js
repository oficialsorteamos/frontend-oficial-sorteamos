import { ref, computed, watch } from '@vue/composition-api'

export default function useCalendarEventHandler(props, emit) {
  // ------------------------------------------------
  // eventLocal
  // ------------------------------------------------
  const settingsLocal = ref(JSON.parse(JSON.stringify(props.fairDistribution.value)))
  
  const resetTransferLocal = () => {
    settingsLocal.value = JSON.parse(JSON.stringify(props.fairDistribution.value))
  }
  watch(props.fairDistribution, () => {
    resetTransferLocal()
  })

  const onSubmit = () => {
    const fairDistributionData = JSON.parse(JSON.stringify(settingsLocal))

    if (props.fairDistribution.value.id) emit('update-fair-distribution', fairDistributionData.value)
    else emit('add-fair-distribution', fairDistributionData.value)
    
    //Fecha o modal
    emit('hide-modal', 'modal-add-fair-distribution')
    emit('hide-modal', 'modal-edit-fair-distribution-'+fairDistributionData.value.id)
  }

  return {
    settingsLocal,

    resetTransferLocal,

    onSubmit,
  }
}

import { ref, computed, watch } from '@vue/composition-api'

export default function useCampaignModalSettingsHandler(props, emit) {
  // ------------------------------------------------
  // eventLocal
  // ------------------------------------------------
  const settingsLocal = ref(JSON.parse(JSON.stringify(props.campaign.value)))
  
  const resetTransferLocal = () => {
    settingsLocal.value = JSON.parse(JSON.stringify(props.campaign.value))
  }
  watch(props.campaign, () => {
    resetTransferLocal()
  })

  const onSubmit = () => {
    const settingsData = JSON.parse(JSON.stringify(settingsLocal))
    //Manda os dados para serem processados
    emit('update-settings', settingsData.value)
    //Fecha o modal
    emit('hide-modal', 'modal-settings-'+settingsLocal.value.id)
  }

  return {
    settingsLocal,

    resetTransferLocal,

    onSubmit,
  }
}

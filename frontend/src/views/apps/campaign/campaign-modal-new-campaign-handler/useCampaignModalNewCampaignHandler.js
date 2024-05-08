import { ref, computed, watch } from '@vue/composition-api'

export default function useCampaignModalEditCampaignHandler(props, emit) {
  // ------------------------------------------------
  // eventLocal
  // ------------------------------------------------
  const campaignLocal = ref(JSON.parse(JSON.stringify(props.campaign.value)))
  
  const resetTransferLocal = () => {
    campaignLocal.value = JSON.parse(JSON.stringify(props.campaign.value))
  }
  watch(props.campaign, () => {
    resetTransferLocal()
  })

  const onSubmit = () => {
    const campaignData = JSON.parse(JSON.stringify(campaignLocal))
    //Manda os dados para serem processados
    if (props.campaign.value.id) emit('update-campaign', campaignData.value)
    else emit('add-campaign', campaignData.value)

    //Fecha o modal associado a um canal específico
    emit('hide-modal', 'modal-edit-campaign-'+props.campaign.value.id)
    emit('hide-modal', 'modal-add-campaign')
  }

  return {
    campaignLocal,

    resetTransferLocal,

    onSubmit,
  }
}

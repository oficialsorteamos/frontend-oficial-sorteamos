import { ref, computed, watch } from '@vue/composition-api'

export default function useCampaignModalChooseTemplateHandler(newTemplateMessageData, emit) {
  // ------------------------------------------------
  // eventLocal
  // ------------------------------------------------
  
  const quickMessageLocal = ref(newTemplateMessageData.value)
  
  /*
  const resetTransferLocal = () => {
    quickMessageLocal.value = JSON.parse(JSON.stringify(props.transfer.value))
  }
  watch(props.transfer, () => {
    resetTransferLocal()
  })*/


  //Função para processamento do template
  const onSubmitQuickMessage = () => {
    const quickMessageData = JSON.parse(JSON.stringify(quickMessageLocal))
    //Manda os dados para serem processados
    emit('add-campaign-quick-message', quickMessageData.value)
    
    //Fecha o modal
    emit('hide-modal', 'modal-choose-quick-message')
  }

  return {

    quickMessageLocal,
    onSubmitQuickMessage,
  }
}

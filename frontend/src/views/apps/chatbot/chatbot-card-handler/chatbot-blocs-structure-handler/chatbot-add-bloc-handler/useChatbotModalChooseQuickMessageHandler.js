import { ref, computed, watch } from '@vue/composition-api'

export default function useCampaignModalChooseTemplateHandler(newTemplateMessageData, emit) {
  // ------------------------------------------------
  // eventLocal
  // ------------------------------------------------
  
  const templateLocal = ref(newTemplateMessageData.value)
  
  /*
  const resetTransferLocal = () => {
    quickMessageLocal.value = JSON.parse(JSON.stringify(props.transfer.value))
  }
  watch(props.transfer, () => {
    resetTransferLocal()
  })*/


  //Função para processamento do template
  const onSubmitQuickMessage = () => {
    const templateData = JSON.parse(JSON.stringify(templateLocal))
    //Manda os dados para serem processados
    emit('add-campaign-template', templateData.value)
    
    //Fecha o modal
    emit('hide-modal', 'modal-choose-template')
  }

  const resolveStatusVariant = role => {
    if (role === 'Enviado') return 'primary'
    if (role === 'Pendente' || role === 'Sinalizado') return 'warning'
    if (role === 'Aprovado') return 'success'
    if (role === 'Reprovado' || role === 'Desativado' || role === 'Erro ao Enviar') return 'danger'
    return 'primary'
  }

  return {

    templateLocal,
    onSubmitQuickMessage,

    resolveStatusVariant,
  }
}

import { ref, computed, watch } from '@vue/composition-api'

export default function useCalendarEventHandler(props, emit) {
  // ------------------------------------------------
  // eventLocal
  // ------------------------------------------------
  const quickMessageLocal = ref(JSON.parse(JSON.stringify(props.quickMessage.value)))
  
  const templateLocal = ref(JSON.parse(JSON.stringify(props.templateMessage.value)))
  
  /*
  const resetTransferLocal = () => {
    quickMessageLocal.value = JSON.parse(JSON.stringify(props.transfer.value))
  }
  watch(props.transfer, () => {
    resetTransferLocal()
  })*/

  const onSubmit = () => {
    const quickMessageData = JSON.parse(JSON.stringify(quickMessageLocal))
    //Manda os dados para serem processados
    emit('send-quick-message', quickMessageData.value)
    
    //Fecha o modal
    emit('hide-modal', 'modal-quick-message')
  }

  //Função para processamento do template
  const onSubmitTemplate = () => {
    const templateData = JSON.parse(JSON.stringify(templateLocal))
    //Manda os dados para serem processados
    emit('send-template-message', templateData.value)
    
    //Fecha o modal
    emit('hide-modal', 'modal-quick-message')
  }

  const resolveStatusVariant = role => {
    if (role === 'Enviado') return 'primary'
    if (role === 'Pendente' || role === 'Sinalizado') return 'warning'
    if (role === 'Aprovado') return 'success'
    if (role === 'Reprovado' || role === 'Desativado' || role === 'Erro ao Enviar') return 'danger'
    return 'primary'
  }

  return {
    quickMessageLocal,
    onSubmit,

    templateLocal,
    onSubmitTemplate,

    resolveStatusVariant,
  }
}

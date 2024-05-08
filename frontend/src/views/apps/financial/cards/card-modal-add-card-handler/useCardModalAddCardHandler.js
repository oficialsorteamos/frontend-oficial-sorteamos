import { ref, computed, watch } from '@vue/composition-api'

export default function useChannelModalEditChannelHandler(props, emit) {
  // ------------------------------------------------
  // eventLocal
  // ------------------------------------------------
  const cardLocal = ref(JSON.parse(JSON.stringify(props.card.value)))
  
  const resetTransferLocal = () => {
    cardLocal.value = JSON.parse(JSON.stringify(props.card.value))
  }
  watch(props.card, () => {
    resetTransferLocal()
  })

  const onSubmit = () => {
    const cardData = JSON.parse(JSON.stringify(cardLocal))
    //Manda os dados para serem processados
    if (props.card.value.id) emit('update-card', cardData.value)
    else emit('add-card', cardData.value)

    //Fecha o modal associado a um canal específico
    emit('hide-modal', 'modal-edit-card-'+props.card.value.id)
    emit('hide-modal', 'modal-add-card')
  }

  return {
    cardLocal,

    resetTransferLocal,

    onSubmit,
  }
}

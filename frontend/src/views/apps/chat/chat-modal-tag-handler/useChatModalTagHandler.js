import { ref, computed, watch } from '@vue/composition-api'

export default function useCalendarEventHandler(props, emit) {
  // ------------------------------------------------
  // eventLocal
  // ------------------------------------------------
  const tagLocal = ref(JSON.parse(JSON.stringify(props.tag.value)))
  
  const resetTransferLocal = () => {
    tagLocal.value = JSON.parse(JSON.stringify(props.tag.value))
  }
  watch(props.tag, () => {
    resetTransferLocal()
  })

  const onSubmit = () => {
    const tagData = JSON.parse(JSON.stringify(tagLocal))
    //Manda os dados para serem processados
    emit('update-tag', tagData.value)
    //Fecha o modal
    emit('hide-modal', 'modal-tag')
  }

  return {
    tagLocal,

    resetTransferLocal,

    onSubmit,
  }
}

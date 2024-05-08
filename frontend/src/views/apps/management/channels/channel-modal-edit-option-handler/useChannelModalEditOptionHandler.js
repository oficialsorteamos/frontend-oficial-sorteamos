import { ref, computed, watch } from '@vue/composition-api'

export default function useChannelModalEditParameterHandler(props, emit) {
  // ------------------------------------------------
  // eventLocal
  // ------------------------------------------------
  const channelLocal = ref(JSON.parse(JSON.stringify(props.channel.value)))
  console.log(channelLocal)
  const resetTransferLocal = () => {
    channelLocal.value = JSON.parse(JSON.stringify(props.channel.value))
  }
  watch(props.channel, () => {
    resetTransferLocal()
  })

  const onSubmit = () => {
    const channelData = JSON.parse(JSON.stringify(channelLocal))
    //Manda os dados para serem processados
    emit('update-parameters-channel', channelData.value)
    //Fecha o modal
    emit('hide-modal', 'modal-edit-parameter-'+channelData.value.id)
    emit('hide-modal', 'modal-edit-option-'+channelData.value.id)
  }

  return {
    channelLocal,

    resetTransferLocal,

    onSubmit,
  }
}

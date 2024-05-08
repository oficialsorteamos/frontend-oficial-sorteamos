import { ref, computed, watch } from '@vue/composition-api'

export default function useChannelModalEditChannelHandler(props, emit) {
  // ------------------------------------------------
  // eventLocal
  // ------------------------------------------------
  const channelLocal = ref(JSON.parse(JSON.stringify(props.channel.value)))
  
  const resetTransferLocal = () => {
    channelLocal.value = JSON.parse(JSON.stringify(props.channel.value))
  }
  watch(props.channel, () => {
    resetTransferLocal()
  })

  const onSubmit = () => {
    const channelData = JSON.parse(JSON.stringify(channelLocal))
    //Manda os dados para serem processados
    if (props.channel.value.id) emit('update-channel', channelData.value)
    else emit('add-channel', channelData.value)

    //Fecha o modal associado a um canal específico
    emit('hide-modal', 'modal-edit-channel-'+props.channel.value.id)
    emit('hide-modal', 'modal-add-channel')
  }

  return {
    channelLocal,

    resetTransferLocal,

    onSubmit,
  }
}

import { ref, computed, watch } from '@vue/composition-api'

export default function useCalendarEventHandler(props, emit) {
  // ------------------------------------------------
  // eventLocal
  // ------------------------------------------------
  const actionLocal = ref(JSON.parse(JSON.stringify(props.action.value)))
  
  const resetActionLocal = () => {
    actionLocal.value = JSON.parse(JSON.stringify(props.action.value))
  }
  watch(props.action, () => {
    resetActionLocal()
  })

  const onSubmit = () => {
    const actionData = JSON.parse(JSON.stringify(actionLocal))

    // * If event has id => Edit Event
    // Emit event for add/update event
    if (props.action.value.id) emit('update-action', actionData.value)
    else emit('add-action', actionData.value)

    console.log('actionData.value.mainBlocId')
    console.log(actionData.value)
    //Fecha o modal
    emit('hide-modal', 'modal-action-'+actionData.value.mainBlocId)
  }

  return {
    actionLocal,

    resetActionLocal,

    onSubmit,
  }
}

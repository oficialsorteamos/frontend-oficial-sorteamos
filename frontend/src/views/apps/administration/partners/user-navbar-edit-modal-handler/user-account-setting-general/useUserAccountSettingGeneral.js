import { ref, computed, watch } from '@vue/composition-api'
import { formatDateOnlyNumber } from '@core/utils/filter'

export default function useCalendarEventHandler(props, emit) {
  // ------------------------------------------------
  // eventLocal
  // ------------------------------------------------
  const userLocal = ref(JSON.parse(JSON.stringify(props.user.value)))

  userLocal.value.birthday = formatDateOnlyNumber(userLocal.value.birthday)
  
  const resetUserLocal = () => {
    userLocal.value = JSON.parse(JSON.stringify(props.user.value))
  }
  watch(props.user, () => {
    resetUserLocal()
  })

  const onSubmit = () => {
    const userData = JSON.parse(JSON.stringify(userLocal))
    //Manda os dados para serem processados
    emit('update-user-information', userData.value)
    //Fecha o modal
    emit('hide-modal', 'modal-edit-user-information')
  }

  return {
    userLocal,

    resetUserLocal,

    onSubmit,
  }
}

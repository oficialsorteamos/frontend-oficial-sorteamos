import { ref, computed, watch } from '@vue/composition-api'

export default function useDepartmentModalHandler(props, emit) {
  // ------------------------------------------------
  // eventLocal
  // ------------------------------------------------
  const paymentLocal = ref(JSON.parse(JSON.stringify(props.channel.value)))
  
  //paymentLocal.credit_card_renewal = props.channel.value.subscription.card
  console.log('props.channel.value')
  console.log(props.channel.value)

  const resetTransferLocal = () => {
    paymentLocal.value = JSON.parse(JSON.stringify(props.channel.value))
  }
  watch(props.channel, () => {
    resetTransferLocal()
  })

  const onSubmit = () => {
    const userData = JSON.parse(JSON.stringify(paymentLocal))
    //Manda os dados para serem processados
    emit('add-payment', userData.value)
    
    if(userData.value.payment_method.id == 3) {
      emit('open-modal', 'modal-pix-qrcode-'+userData.value.id)
    }

    //Fecha o modal DE adição de usuário
    emit('hide-modal', 'modal-add-payment-'+userData.value.id)
  }

  const onSubmitSubscription = () => {
    const userData = JSON.parse(JSON.stringify(paymentLocal))
    //Manda os dados para serem processados
     emit('update-subscription', userData.value)

    //Fecha o modal DE adição de usuário
    //emit('hide-modal', 'modal-add-payment-'+userData.value.id)
  }

  return {
    paymentLocal,

    resetTransferLocal,

    onSubmit,
    onSubmitSubscription,
  }
}

<template>
  <div class="chats">
    <div class="text-center">
        <b-button
          variant="outline-primary"
          class="btn-icon rounded-circle"
          @click="$emit('load-messages', offset); sumOffset();"
          :hidden="hiddenButtonMoreMessage || loadingMessages"
          v-b-tooltip.hover.v-secondary
          :title="$t('chat.chatLog.showMore')"
        >
          <feather-icon 
            icon="PlusIcon" 
            size="20"
          />
        </b-button>
        <b-spinner 
          :label="$t('chat.chatLog.loading')+'...'" 
          v-show="loadingMessages"
        />
      </div>
    <div
      v-for="(msgGrp, index) in formattedChatData.formattedChatLog"
      :key="msgGrp.senderId+String(index)"
      class="chat"
      :class="{'chat-left': msgGrp.typeUserId === 2}"
    >
      <!-- Se a mensagem for a primeira de um atendimento -->
      <div 
        v-if="msgGrp.messageId == msgGrp.firstMessageServiceId && msgGrp.protocolNumber && msgGrp.messages.length > 0"
        class="mt-5 mb-1 text-center"
      >
        <b-badge
          variant="success"
          class="badge-glow"
        >
          {{ $t('chat.chatLog.protocolServiceNumber') }}: {{ msgGrp.protocolNumber }}
        </b-badge>
      </div>
      <div class="chat-avatar">
        <b-avatar
          size="36"
          class="avatar-border-2 box-shadow-1"
          :variant="formattedChatData.contact.avatar != null ? 'light-primary' : 'light-'+formattedChatData.contact.avatarColor"
          :text="msgGrp.typeUserId === 2 && formattedChatData.contact.initialsName != null ? formattedChatData.contact.initialsName : avatarText(profileUser.name)"
          :src="msgGrp.typeUserId === 2? formattedChatData.contact.avatar? baseUrlStorage+formattedChatData.contact.avatar: null : baseUrlStorage+profileUser.avatar"
          v-if="msgGrp.messages.length > 0"
        />
      </div>
      <div class="chat-body">
        <div
          v-for="(msgData, index) in msgGrp.messages"
          :key="msgData.time+String(index)"
          :class="msgData.chatContent"
        >
          <div
            class="text-right"
          >
            <b-dropdown
              variant="link"
              no-caret
              dropright
              v-if="msgGrp.typeUserId == 2"
            >

              <template #button-content>
                <feather-icon
                  icon="ChevronDownIcon"
                  size="20"
                  class="align-right text-body"
                  :stroke="msgGrp.typeUserId === 2? '#808080' : '#FFF'"
                />
              </template>

              <b-dropdown-item 
                @click="$emit('set-reply-api-message-id', msgData)"
              >  
                <span class="align-middle ml-50">Responder</span>
              </b-dropdown-item>
              <!--
              <b-dropdown-item
                @click="removeDepartment(data.item.id)"
              >
                <feather-icon icon="TrashIcon" />
                <span class="align-middle ml-50">Delete</span>
              </b-dropdown-item>
              -->
            </b-dropdown>
            <b-dropdown
              variant="link"
              no-caret
              dropleft
              v-else
            >

              <template #button-content>
                <feather-icon
                  icon="ChevronDownIcon"
                  size="20"
                  class="align-right text-body"
                  :stroke="msgGrp.typeUserId === 2? '#808080' : '#FFF'"
                />
              </template>

              <b-dropdown-item 
                @click="$emit('set-reply-api-message-id', msgData)"
              >  
                <span class="align-middle ml-50">Responder</span>
              </b-dropdown-item>
              <!--
              <b-dropdown-item
                @click="removeDepartment(data.item.id)"
              >
                <feather-icon icon="TrashIcon" />
                <span class="align-middle ml-50">Delete</span>
              </b-dropdown-item>
              -->
            </b-dropdown>
          </div> 
            
            <!-- Se foi o CONTATO que mandou a mensagem a origem da mensagem for um SMS -->
            <span
              v-if="msgGrp.typeUserId == 2 && msgData.typeOriginMessageId == 2"
            >
              <b-badge
                variant="success"
                class="badge-glow mb-1"
                style="font-size: 9px"
              >
                SMS
              </b-badge>
            </span>

            <!-- Se for uma mensagem de resposta a outra mensagem -->
            <span
              v-if="msgData.answeredMessage"
            >
              <!-- Se a mensagem respondida for um texto -->
              <span
                v-if="msgData.answeredMessage.type_message_chat_id == 1"
              >
                <div
                  class="rounded pb-1 pl-1 pr-1 mb-1"
                  :style="msgGrp.typeUserId == 2? 'background-color: #ededed' : 'background-color: #454545'"
                >
                  {{ msgData.answeredMessage.mes_message }}
                </div>
              </span>
              <!-- Se a mensagem respondida for uma imagem -->
              <span
                v-else-if="msgData.answeredMessage.type_message_chat_id == 3"
              >
                <div
                  class="rounded px-1 py-1 mb-1"
                  style="background-color: #ededed"
                >
                  <b-img
                    thumbnail
                    fluid
                    :src="baseUrlStorage+`public/chats/chat${msgData.answeredMessage.chat_id}/images/${msgData.answeredMessage.mes_content_name}`"
                    style="max-width: 120px; cursor: pointer"
                  />
                </div>
              </span>
              <!-- Se a mensagem respondida for um arquivo -->
              <span
                v-else-if="msgData.answeredMessage.type_message_chat_id == 5"
              >
                <div
                  class="rounded px-1 py-1 mb-1"
                  style="background-color: #ededed"
                >
                  <a 
                    target="_blank" 
                    :href="baseUrlStorage+`public/chats/chat${msgData.answeredMessage.chat_id}/files/${msgData.answeredMessage.mes_content_name}`"
                    :class="{'link-white-color': msgGrp.typeUserId != 2}"> 
                      <span>{{ msgData.answeredMessage.mes_media_original_name }}</span>
                  </a>
                </div>
              </span>
              <!-- Se for um áudio -->
              <span
                v-else-if="msgData.answeredMessage.type_message_chat_id == 2"
              >
                <div
                  class="rounded px-1 py-1 mb-1"
                  style="background-color: #ededed"
                >
                  <span
                    v-if="!msgData.answeredMessage.quick_message_id"
                  >
                    <audio controls>
                      <source :src="baseUrlStorage+`public/chats/chat${msgData.answeredMessage.chat_id}/audios/${msgData.answeredMessage.mes_content_name}`" :type="msgData.answeredMessage.mes_content_type">
                    </audio>
                  </span>
                  <!-- Se for um áudio proveniente de uma mensagem rápida -->
                  <span
                    v-else
                  >
                    <audio controls>
                      <source :src="baseUrlStorage+`public/quickMessages/quickMessage${msgData.answeredMessage.parameters[0].quick_message_id}/header/${msgData.answeredMessage.parameters[0].media_name}`" type="audio/mpeg">
                      Your browser does not support the audio tag.  
                    </audio>
                  </span>
                </div>
              </span>
              <!-- Se for um vídeo -->
              <span
                v-else-if="msgData.answeredMessage.type_message_chat_id == 4"
              >
                <div
                  class="rounded px-1 py-1 mb-1"
                  style="background-color: #ededed"
                >
                  <video width="280" height="200" controls>
                    <source :src="baseUrlStorage+`public/chats/chat${msgData.answeredMessage.chat_id}/videos/${msgData.answeredMessage.mes_content_name}`" :type="msgData.answeredMessage.mes_content_type">
                  </video>
                </div>
              </span>
            </span>

            <!-- Caso seja uma mensagem de texto -->
            <p v-if="msgData.typeMessageId == 1">
              <!-- Se houver uma mensgem rápida com mídia associada à messagem -->
              <span
                v-if="msgData.quickMessageId"
              >
                <span
                  v-if="msgData.parameters[0] && msgData.parameters[0].type_parameter_id == 2"
                >
                  <viewer 
                    :options="{inline: false, button: false, navbar: false, title: false, toolbar: true, tooltip: true, movable: false, zoomable: true, rotatable: true, scalable: true, transition: true, fullscreen: true, keyboard: true}"
                    class="text-center"
                  >
                    <b-img
                      thumbnail
                      fluid
                      :src="baseUrlStorage+`public/quickMessages/quickMessage${msgData.parameters[0].quick_message_id}/header/${msgData.parameters[0].media_name}`"
                      style="max-width: 200px; cursor: pointer; margin-bottom: 10px"
                    />
                  </viewer>
                </span>
                <span
                  v-if="msgData.parameters[0] && msgData.parameters[0].type_parameter_id == 5"
                >
                  <div
                    class="text-center"
                  >
                    <video width="320" height="240" controls>
                      <source :src="baseUrlStorage+`public/quickMessages/quickMessage${msgData.parameters[0].quick_message_id}/header/${msgData.parameters[0].media_name}`" :type="msgData.contentType">
                    </video>
                  </div>
                </span>
            </span>
              <!-- Se a mensagem NÃO é uma mensagem deletada pelo contato -->
              <span v-if="msgData.statusMessageChatId != 6"><span v-if="!msgData.waitingMessage">{{ msgData.msg }}</span><span v-else><i>{{ msgData.msg }}</i> <feather-icon icon="ClockIcon" size="12"/></span></span>
              <span v-else><s>{{ msgData.msg }}</s></span>
              <span
                v-if="msgData.parameters"
              >
                <span
                  v-for="(parameter, index) in msgData.parameters"
                  :key="parameter.id"
                  style="display: block; text-align: -webkit-center; margin-right: 20px;"
                >
                  <!-- Se o parâmetro for um BOTÃO -->
                  <div
                    :class="index == 0? 'rounded border-white box-button ml-2 mt-2' : 'rounded border-white box-button ml-2'"
                    v-if="(parameter.quick_message_id && parameter.type_parameter_id == 1) || (parameter.template_id && parameter.type_parameter_id == 2)"
                  >
                    <div
                      class=" text-center"
                      style="padding-top: 7px"
                    >
                      <!-- Se for um botão de RESPOSTA RÁPIDA -->
                      <span
                        v-if="parameter.url == null && parameter.phone_number == null"
                      >{{ parameter.content }}
                      </span>
                      <!-- Se for um botão de chamada para ação -->
                      <span
                        v-else
                      >
                        <!-- Se a ação for uma url -->
                        <span
                          v-if="parameter.url"
                        >
                          <feather-icon
                            icon="ExternalLinkIcon"
                            size="16"
                            style="vertical-align: text-top"
                          />
                        </span>
                        <!-- Se a ação for um número de telefone -->
                        <span
                          v-else
                        >
                          <feather-icon
                            icon="PhoneIcon"
                            size="16"
                            style="vertical-align: text-top"
                          />
                        </span> {{ parameter.content }}
                      </span>
                    </div>
                  </div>
                </span>
              </span>
              
            </p>
            <!-- Caso seja um arquivo -->
            <p v-else-if="msgData.typeMessageId == 5">
              <span
                v-if="!msgData.quickMessageId"
              >
                <a 
                  target="_blank" 
                  :href="baseUrlStorage+`public/chats/chat${msgData.chatId}/files/${msgData.contentName}`"
                  :class="{'link-white-color': msgGrp.typeUserId != 2}"
                > 
                  <span>{{ msgData.mesMediaOriginalName }}</span>
                </a>
                <br>
                <span>{{ msgData.contentCaption }}</span>  
              </span>
              <span
                v-else
              >
                <a 
                  target="_blank" 
                  :href="baseUrlStorage+`public/quickMessages/quickMessage${msgData.parameters[0].quick_message_id}/header/${msgData.parameters[0].media_name}`"
                  :class="{'link-white-color': msgGrp.typeUserId != 2}"
                > 
                  <span>{{ msgData.parameters[0].media_original_name }}</span>
                </a>
              </span>
            </p>
            <!-- Caso seja uma imagem -->
            <p v-else-if="msgData.typeMessageId == 3">
              <viewer 
                :options="{inline: false, button: false, navbar: false, title: false, toolbar: true, tooltip: true, movable: false, zoomable: true, rotatable: true, scalable: true, transition: true, fullscreen: true, keyboard: true}"
                class="text-center"
              >
                <b-img
                  thumbnail
                  fluid
                  :src="baseUrlStorage+`public/chats/chat${msgData.chatId}/images/${msgData.contentName}`"
                  style="max-width: 200px; cursor: pointer; margin-bottom: 10px"
                />
              </viewer>
              <span>{{ msgData.contentCaption }}</span>
            </p>
            <!-- Caso seja um áudio -->
            <p v-else-if="msgData.typeMessageId == 2">
              <span
                v-if="!msgData.quickMessageId"
              >
                <audio controls>
                  <source :src="baseUrlStorage+`public/chats/chat${msgData.chatId}/audios/${msgData.contentName}`" :type="msgData.contentType">
                </audio>
              </span>
              <!-- Se for um áudio proveniente de uma mensagem rápida -->
              <span
                v-else
              >
                <audio controls>
                  <source :src="baseUrlStorage+`public/quickMessages/quickMessage${msgData.parameters[0].quick_message_id}/header/${msgData.parameters[0].media_name}`" type="audio/mpeg">
                  Your browser does not support the audio tag.  
                </audio>
              </span>
            </p>
            <!-- Caso seja um vídeo -->
            <p v-else-if="msgData.typeMessageId == 4" class="text-center">
              <span
                v-if="!msgData.quickMessageId"
              >
                <video width="320" height="240" controls>
                  <source :src="baseUrlStorage+`public/chats/chat${msgData.chatId}/videos/${msgData.contentName}`" :type="msgData.contentType">
                </video>
                <br>
                <span>{{ msgData.contentCaption }}</span>
              </span>
              <span
                v-else
              >
                <video width="320" height="240" controls>
                  <source :src="baseUrlStorage+`public/quickMessages/quickMessage${msgData.parameters[0].quick_message_id}/header/${msgData.parameters[0].media_name}`" :type="msgData.contentType">
                </video>
                <br>
                <span>{{ msgData.parameters[0].media_original_name }}</span>
              </span>
            </p>
            <!-- Caso seja um contato compartilhado -->
            <p v-else-if="msgData.typeMessageId == 6">
              <span class="d-flex justify-content-start h-100">
                <b-avatar
                  size="50"
                  src=""
                  class="avatar-border-2 box-shadow-1"
                  variant="light-primary"
                  :text="msgData.contactName.length > 0? avatarText(msgData.contactName) : 'C'"
                />
                <div class="d-flex ml-1">
                  <div style="margin-top: -9px">
                    <h5 
                      class="mb-0"
                      v-b-tooltip.hover.v-secondary
                      :title="msgData.contactName.length > 20? msgData.contactName : ''"  
                    >
                      {{ msgData.contactName.length > 20? msgData.contactName.substring(0,20)+'...' : msgData.contactName }}
                    </h5>
                    <p >
                      <em>{{ msgData.contactPhoneNumber | VMask(' +## (##) #####-####') }}</em>
                    </p>
                  </div>
                </div>
              </span>
            </p>
            <!-- Caso seja um stiker -->
            <p v-else-if="msgData.typeMessageId == 7">
              <viewer 
                :options="{inline: false, button: false, navbar: false, title: false, toolbar: true, tooltip: true, movable: false, zoomable: true, rotatable: true, scalable: true, transition: true, fullscreen: true, keyboard: true}"
              >
                <b-img
                  thumbnail
                  fluid
                  :src="baseUrlStorage+`public/chats/chat${msgData.chatId}/stikers/${msgData.contentName}`"
                  style="max-width: 200px; cursor: pointer"
                />
              </viewer>
            </p>
            <!-- Caso seja uma localização -->
            <p v-else-if="msgData.typeMessageId == 8">
              <iframe 
                width="450" 
                height="250" 
                frameborder="0" 
                scrolling="no" 
                marginheight="0" 
                marginwidth="0" 
                :src="'https://maps.google.com/maps?q='+msgData.latitude+','+msgData.longitude+'&hl=es&z=14&amp;output=embed'"
              >
              </iframe>
            </p>
            <!-- Caso seja uma ligação -->
            <p v-else-if="msgData.typeMessageId == 9">
              <span
                v-if="!msgData.quickMessageId"
              >
                <audio controls>
                  <source :src="baseUrlStorage+`public/chats/chat${msgData.chatId}/calls/${msgData.contentName}`" :type="msgData.contentType">
                </audio>
              </span>
            </p>
            <div class="d-flex pt-1">
              <span style="font-size: 9px;" :class="msgGrp.typeUserId == 2? 'mr-2' : ''" :title="formatDate(msgData.time)">
                {{ formatDateToMonthShortWithTime(msgData.time, { hour: 'numeric', minute: 'numeric' }) }}
              </span>
              <!-- Se a mensagem NÃO foi enviada por um CONTATO e NÃO seja uma mensagem privada -->
              <span
                style="margin-top: 7px; margin-left: 4px; margin-right: 12px;"
                v-if="msgGrp.typeUserId != 2 && msgData.privateMessage == 0 && msgData.typeMessageId != 9"
              >
                <!-- Se mensagem está com status de ENFILEIRADA -->
                <font-awesome-icon 
                  :icon="['fas', 'clock']" 
                  size="1x"
                  style="height: 0.6em"
                  v-if="msgData.statusMessageChatId == 1"
                  :title="$t('chat.chatLog.queued')"
                />
                <!-- Se mensagem está com status de ENVIADA -->
                <font-awesome-icon 
                  :icon="['fas', 'check']" 
                  size="1x"
                  style="height: 0.6em"
                  v-if="msgData.statusMessageChatId == 2"
                  :title="$t('chat.chatLog.sent')"
                />
                <!-- Se mensagem está com status de ENTREGUE ou LIDA -->
                <font-awesome-icon 
                  :icon="['fas', 'check-double']" 
                  size="1x"
                  style="height: 0.6em"
                  v-if="msgData.statusMessageChatId == 3 || msgData.statusMessageChatId == 5"
                  :title="$t('chat.chatLog.delivered')"
                />
                <!-- Se mensagem está com status de ERRO AO ENVIAR -->
                <font-awesome-icon 
                  :icon="['fas', 'exclamation-triangle']" 
                  size="1x"
                  style="height: 0.6em"
                  v-if="msgData.statusMessageChatId == 4"
                  :title="$t('chat.chatLog.failedSend')"
                />
              </span>
            </div>
            
            <div
              class="float-right text-right ml-1"
              style="margin-top: -20px"
            >
              <span>
                <feather-icon 
                  icon="PhoneIcon"
                  size="10"
                  v-b-tooltip.hover.v-secondary
                  v-if="msgGrp.typeUserId == 2 && msgData.phoneChannelReceivedMessage"
                  :title="'Mensagem Enviada para o canal ' + msgData.phoneChannelReceivedMessage | VMask(msgData.phoneChannelReceivedMessage.substr(0, 4) != '0800'? msgData.phoneChannelReceivedMessage.length == 13? 'XXXXXXXXXXXXXXXXXXXXXXXXXXXXXX +## (##) #####-####' : 'XXXXXXXXXXXXXXXXXXXXXXXXXXXXXX +## (##) ####-####'  : 'XXXXXXXXXXXXXXXXXXXXXXXXXXXXXX +## #### ### ####')"
                />
                <feather-icon 
                  icon="PhoneIcon"
                  size="10"
                  v-b-tooltip.hover.v-secondary
                  v-else-if="msgGrp.typeUserId != 2 && msgData.phoneChannelSentMessage"
                  :title="'Mensagem enviada pelo canal ' +msgData.phoneChannelSentMessage | VMask(msgData.phoneChannelSentMessage.substr(0, 4) != '0800'? msgData.phoneChannelSentMessage.length == 13? 'XXXXXXXXXXXXXXXXXXXXXXXXXXXX +## (##) #####-####' : 'XXXXXXXXXXXXXXXXXXXXXXXXXXXX +## (##) ####-####'  : 'XXXXXXXXXXXXXXXXXXXXXXXXXXXX +## #### ### ####')"
                />
              </span>
              <!-- Se for uma mensagem privada -->
              <span 
                v-if="msgData.privateMessage == 1 && msgData.typeMessageId != 9"
              >
                <feather-icon 
                  icon="LockIcon"
                  size="10"
                />
              </span>
              <span
                v-else-if="msgData.typeMessageId == 9"
              >
                <feather-icon
                  icon="PhoneOutgoingIcon"
                  size="16"
                  stroke="#00e500"
                />
              </span>
            </div>
            <!-- Se houve algum erro ao enviar a mensagem-->
            <div
            class="float-right text-right"
            v-if="msgData.sendSuccess == false || msgData.statusMessageChatId == 4"
            >
              <b-button
                variant="dark"
                class="btn-icon rounded-circle"
                v-b-tooltip.hover.v-secondary
                :title="$t('chat.chatLog.resendMessage')"
                @click="$emit('resend-message', msgData);"
                :disabled="msgData.spinner"
              >
                <feather-icon 
                  icon="SendIcon" 
                  size="12"
                  v-show="!msgData.spinner"
                />
                <b-spinner 
                  small
                  :label="$t('chat.chatLog.loading')+'...'" 
                  v-show="msgData.spinner"
                />
              </b-button>
            </div>
          </div>   
      </div>
    </div>
    <div
      class="px-2 py-2 rounded"
      style="background-color: #f2f2f2"
      v-if="messageReply"
    >
    <b-row
      style="margin-top: 20px"
    >
      <b-col
        cols="11"
        md="11"
      >
        <!-- Caso seja uma mensagem de texto -->
        <p v-if="messageReply.typeMessageId == 1">
          {{messageReply.msg}}
        </p>
        <!-- Se for um arquivo -->
        <p v-else-if="messageReply.typeMessageId == 5">
          <span
            v-if="!messageReply.quickMessageId"
          >
            <a 
              target="_blank" 
              :href="baseUrlStorage+`public/chats/chat${messageReply.chatId}/files/${messageReply.contentName}`"
              :class="{'link-white-color': msgGrp.typeUserId != 2}"
            > 
              <span>{{ messageReply.mesMediaOriginalName }}</span>
            </a>
            <br>
            <span>{{ messageReply.contentCaption }}</span>  
          </span>
          <span
            v-else
          >
            <a 
              target="_blank" 
              :href="baseUrlStorage+`public/quickMessages/quickMessage${messageReply.parameters[0].quick_message_id}/header/${messageReply.parameters[0].media_name}`"
              :class="{'link-white-color': msgGrp.typeUserId != 2}"
            > 
              <span>{{ messageReply.parameters[0].media_original_name }}</span>
            </a>
          </span>
        </p>
        <!-- Caso seja uma imagem -->
        <p v-else-if="messageReply.typeMessageId == 3">
          <viewer 
            :options="{inline: false, button: false, navbar: false, title: false, toolbar: true, tooltip: true, movable: false, zoomable: true, rotatable: true, scalable: true, transition: true, fullscreen: true, keyboard: true}"
          >
            <b-img
              thumbnail
              fluid
              :src="baseUrlStorage+`public/chats/chat${messageReply.chatId}/images/${messageReply.contentName}`"
              style="max-width: 200px; cursor: pointer; margin-bottom: 10px"
            />
          </viewer>
          <span>{{ messageReply.contentCaption }}</span>
        </p>
        <!-- Caso seja um áudio -->
        <p v-else-if="messageReply.typeMessageId == 2">
          <span
            v-if="!messageReply.quickMessageId"
          >
            <audio controls>
              <source :src="baseUrlStorage+`public/chats/chat${messageReply.chatId}/audios/${messageReply.contentName}`" :type="messageReply.contentType">
            </audio>
          </span>
          <!-- Se for um áudio proveniente de uma mensagem rápida -->
          <span
            v-else
          >
            <audio controls>
              <source :src="baseUrlStorage+`public/quickMessages/quickMessage${messageReply.parameters[0].quick_message_id}/header/${messageReply.parameters[0].media_name}`" type="audio/mpeg">
              Your browser does not support the audio tag.  
            </audio>
          </span>
        </p>
        <!-- Caso seja um vídeo -->
        <p v-else-if="messageReply.typeMessageId == 4" class="text-center">
          <video width="320" height="240" controls>
            <source :src="baseUrlStorage+`public/chats/chat${messageReply.chatId}/videos/${messageReply.contentName}`" :type="messageReply.contentType">
          </video>
          <br>
          <span>{{ messageReply.contentCaption }}</span>
        </p>
        <!-- Caso seja um contato compartilhado -->
        <p v-else-if="messageReply.typeMessageId == 6">
          <span class="d-flex justify-content-start h-100">
            <b-avatar
              size="50"
              src=""
              class="avatar-border-2 box-shadow-1"
              variant="light-primary"
              :text="messageReply.contactName.length > 0? avatarText(messageReply.contactName) : 'C'"
            />
            <div class="d-flex ml-1">
              <div style="margin-top: -9px">
                <h5 
                  class="mb-0"
                  v-b-tooltip.hover.v-secondary
                  :title="messageReply.contactName.length > 20? messageReply.contactName : ''"  
                >
                  {{ messageReply.contactName.length > 20? messageReply.contactName.substring(0,20)+'...' : messageReply.contactName }}
                </h5>
                <p >
                  <em>{{ messageReply.contactPhoneNumber | VMask(' +## (##) #####-####') }}</em>
                </p>
              </div>
            </div>
          </span>
        </p>
        <!-- Caso seja um stiker -->
        <p v-else-if="messageReply.typeMessageId == 7">
          <viewer 
            :options="{inline: false, button: false, navbar: false, title: false, toolbar: true, tooltip: true, movable: false, zoomable: true, rotatable: true, scalable: true, transition: true, fullscreen: true, keyboard: true}"
          >
            <b-img
              thumbnail
              fluid
              :src="baseUrlStorage+`public/chats/chat${messageReply.chatId}/stikers/${messageReply.contentName}`"
              style="max-width: 200px; cursor: pointer"
            />
          </viewer>
        </p>
        <!-- Caso seja uma localização -->
        <p v-else-if="messageReply.typeMessageId == 8">
          <iframe 
            width="450" 
            height="250" 
            frameborder="0" 
            scrolling="no" 
            marginheight="0" 
            marginwidth="0" 
            :src="'https://maps.google.com/maps?q='+messageReply.latitude+','+messageReply.longitude+'&hl=es&z=14&amp;output=embed'"
          >
          </iframe>
        </p>
      </b-col>
      <b-col
        cols="1"
        md="1"
        class="align-items-center"
      >
        <feather-icon 
          icon="XIcon" 
          size="16"
          class="cursor-pointer"
          @click="$emit('close-reply-box', null)"
        />
      </b-col>
    </b-row>
    </div>
  </div>
</template>

<script>
import 'viewerjs/dist/viewer.css'
import { computed, ref, watch } from '@vue/composition-api'
import { BImg, BAvatar, BButton, VBTooltip, BSpinner, BBadge, BDropdown, BDropdownItem, BRow, BCol} from 'bootstrap-vue'
import { formatDateToMonthShortWithTime, formatDate, avatarText } from '@core/utils/filter'
import { VueMaskFilter } from 'v-mask'
import { faCheck, faCheckDouble, faClock, faExclamationTriangle } from '@fortawesome/free-solid-svg-icons'
import { library } from '@fortawesome/fontawesome-svg-core'
import VueViewer from 'v-viewer'
import Vue from 'vue'
Vue.use(VueViewer)
Vue.filter('VMask', VueMaskFilter)
library.add(faCheck, faCheckDouble, faClock, faExclamationTriangle)

export default {
  components: {
    BImg,
    BAvatar,
    BButton,
    BSpinner,
    BBadge,
    BDropdown,
    BDropdownItem,
    BRow,
    BCol,
    formatDateToMonthShortWithTime,
    formatDate,
    VueViewer,
  },
  directives: {
    'b-tooltip': VBTooltip,
  },
  props: {
    chatData: {
      type: Object,
      required: true,
    },
    hiddenButtonMoreMessage: {
      type: Boolean,
      required: true,
    },
    loadingMessages: {
      type: Boolean,
      required: false,
    },
    profileUser: {
      type: Object,
      required: true,
    },
    baseUrlStorage: {
      type: String,
      required: true,
    },
    messageReply: {
      type: Object,
      required: false,
    },
  },
  setup(props, { emit } ) {

    const formattedChatData = computed(() => {
      const contact = {
        id: props.chatData.contact.id,
        avatar: props.chatData.contact.con_avatar,
        avatarColor: props.chatData.contact.avatarColor,
        initialsName: props.chatData.contact.initialsName,
      }

      //Se existir alguma conversa no chat
      let chatLog = []
      if (props.chatData.chat) {
        chatLog = props.chatData.chat.chat
      }

      //console.log('chatLog')
      //console.log(chatLog)

      const formattedChatLog = []
      
      //Traz o id do usuário que enviou a primeira mensagem
      let chatMessageSenderId = chatLog[0] ? chatLog[0].senderId : undefined
      let typeUserId = chatLog[0] ? chatLog[0].type_user_id : undefined
      let messageId = chatLog[0] ? chatLog[0].id : undefined
      let firstMessageServiceId = chatLog[0] ? chatLog[0].first_message_service : undefined
      let protocolNumber = chatLog[0] ? chatLog[0].ser_protocol_number : undefined
      let msgGroup = {
        sender: chatMessageSenderId,
        typeUserId: typeUserId,
        messageId: messageId,
        firstMessageServiceId: firstMessageServiceId,
        protocolNumber: protocolNumber,

        messages: [],
      }

      //Para cada mensagem
      chatLog.forEach((msg, index) => {
        //console.log(msg)
        //Se a mensagem a ser exibida não teve erro ao ser enviada
        if(msg.status_message_chat_id != 4 && msg.sendSuccess != false) {
          //Se o usuário que enviou a primeira mensagem for o usuário que enviou a mensagem em questão
          if (chatMessageSenderId === msg.senderId && typeUserId === msg.type_user_id) {
            let senderIdData = msg.senderId? msg.senderId : msg.sender_id
            //Insere a mensagem
            msgGroup.messages.push({
              messageId: msg.id,
              apiMessageId: msg.api_message_id,
              senderId: msg.sender_id,
              templateId: msg.template_id,
              quickMessageId: msg.quick_message_id,
              typeOriginMessageId: msg.type_origin_message_id,
              chatId: msg.chat_id,
              serviceId: msg.service_id,
              msg: msg.mes_message,
              firstMessageServiceId: msg.first_message_service, //Id da primeira mensagem de um atendimento 
              typeMessageId: msg.type_message_chat_id,
              contentType: msg.mes_content_type,
              urlMessage: msg.mes_url,
              contentName: msg.mes_content_name,
              contentCaption: msg.mes_caption,
              mesMediaOriginalName: msg.mes_media_original_name,
              time: msg.created_at,
              privateMessage: msg.mes_private,
              waitingMessage: msg.mes_waiting_message,
              typeUserId: msg.type_user_id,
              latitude: msg.mes_lat,
              longitude: msg.mes_long,
              sendSuccess: msg.sendSuccess,
              statusMessageChatId: msg.status_message_chat_id,
              contactName: msg.mes_contact_name,
              contactPhoneNumber: msg.mes_contact_phone_number,
              parameters: msg.parameters,
              answeredMessage: msg.answeredMessage,
              phoneChannelReceivedMessage: msg.mes_phone_channel_received_message,
              phoneChannelSentMessage: msg.mes_phone_channel_sent_message,
              chatContent: senderIdData !=3? (msg.type_user_id == 4? 'chat-content-manager' : 'chat-content') : 'chat-content-external-user', //Se o usuário for GESTOR, aplica a classe chat-content-manager, se for operador, aplica chat-content, se mensagem de usuário externo, aplica chat-content-external-user
            })
          } else { //Se for a primeira mensagem do conjunto de mensagens do contato|operador
            let senderIdData = msg.senderId? msg.senderId : msg.sender_id
            //Guarda o id do usuário que enviou a mensagem
            chatMessageSenderId = msg.senderId
            typeUserId = msg.type_user_id
            messageId = msg.id,
            firstMessageServiceId = msg.first_message_service,
            protocolNumber = msg.ser_protocol_number,
            
            //Insere a mensagem
            formattedChatLog.push(msgGroup)
            msgGroup = {
              senderId: msg.senderId,
              typeUserId: msg.type_user_id,
              messageId: msg.id,
              firstMessageServiceId: msg.first_message_service,
              protocolNumber: msg.ser_protocol_number,
              messages: [{
                messageId: msg.id,
                apiMessageId: msg.api_message_id,
                senderId: msg.sender_id,
                templateId: msg.template_id,
                quickMessageId: msg.quick_message_id,
                typeOriginMessageId: msg.type_origin_message_id,
                chatId: msg.chat_id,
                serviceId: msg.service_id,
                msg: msg.mes_message,
                typeMessageId: msg.type_message_chat_id,
                firstMessageServiceId: msg.first_message_service, //Id da primeira mensagem de um atendimento
                contentType: msg.mes_content_type,
                urlMessage: msg.mes_url,
                contentName: msg.mes_content_name,
                contentCaption: msg.mes_caption,
                mesMediaOriginalName: msg.mes_media_original_name,
                latitude: msg.mes_lat,
                longitude: msg.mes_long,
                time: msg.created_at,
                privateMessage: msg.mes_private,
                sendSuccess: msg.sendSuccess,
                statusMessageChatId: msg.status_message_chat_id,
                contactName: msg.mes_contact_name,
                contactPhoneNumber: msg.mes_contact_phone_number,
                parameters: msg.parameters,
                answeredMessage: msg.answeredMessage,
                phoneChannelReceivedMessage: msg.mes_phone_channel_received_message,
                phoneChannelSentMessage: msg.mes_phone_channel_sent_message,
                chatContent: senderIdData !=3? (msg.type_user_id == 4? 'chat-content-manager' : 'chat-content') : 'chat-content-external-user', //Se o usuário for GESTOR, aplica a classe chat-content-manager, se for operador, aplica chat-content, se mensagem de usuário externo, aplica chat-content-external-user
              }],
            }
          }//Se a mensagem a ser enviada recebeu algum ERRO ao ser enviada
        } else {
          //Se o usuário que tentou enviar a mensagem é o usuário que está logado no sistema ou se foi o robô que tentou enviar
          if (props.profileUser.id == msg.senderId || props.profileUser.id == msg.sender_id || msg.sender_id == 1) {
            //Guarda o id do usuário que enviou a mensagem
            chatMessageSenderId = msg.senderId
            typeUserId = msg.type_user_id
            //Insere a mensagem
            formattedChatLog.push(msgGroup)
            msgGroup = {
              senderId: msg.senderId,
              typeUserId: msg.type_user_id,
              messageId: msg.id,
              firstMessageServiceId: msg.first_message_service,
              protocolNumber: msg.ser_protocol_number,
              messages: [{
                messageId: msg.id,
                apiMessageId: msg.api_message_id,
                senderId: msg.sender_id,
                templateId: msg.template_id,
                quickMessageId: msg.quick_message_id,
                typeOriginMessageId: msg.type_origin_message_id,
                chatId: msg.chat_id,
                serviceId: msg.service_id,
                msg: msg.mes_message,
                typeMessageId: msg.type_message_chat_id,
                firstMessageServiceId: msg.first_message_service, //Id da primeira mensagem de um atendimento
                contentType: msg.mes_content_type,
                urlMessage: msg.mes_url,
                contentName: msg.mes_content_name,
                contentCaption: msg.mes_caption,
                mesMediaOriginalName: msg.mes_media_original_name,
                latitude: msg.mes_lat,
                longitude: msg.mes_long,
                time: msg.created_at,
                privateMessage: msg.mes_private,
                parameters: msg.parameters,
                answeredMessage: msg.answeredMessage,
                phoneChannelReceivedMessage: msg.mes_phone_channel_received_message,
                phoneChannelSentMessage: msg.mes_phone_channel_sent_message,
                sendSuccess: msg.sendSuccess,
                statusMessageChatId: msg.status_message_chat_id,
                chatContent: 'chat-content-error',
                typeUserId: msg.type_user_id,
                spinner: msg.loadingSpinner,
              }],
            }
          }
        }
        if (index === chatLog.length - 1) formattedChatLog.push(msgGroup)
      })

      return {
        formattedChatLog,
        contact,
        profileUser: props.profileUser,
      }
    })

    const offset = ref(1)

    //Toda a vez que o usuário (operador) troca de chat, reinicia o offset com o valor 1 
    watch(() => props.chatData, () => {
      offset.value = 1
    })

    const sumOffset = () => {
      offset.value++ 
    }


    return {
      formattedChatData,
      formatDateToMonthShortWithTime,
      formatDate,
      VueViewer,
      avatarText,

      offset,
      sumOffset,

    }
  },
}
</script>
<style lang="scss" scoped>
.box-button {
  margin-top: 5px;
  width: 205px;
  height: 40px;
  box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2), 0 6px 20px 0 rgba(0, 0, 0, 0.19);
  font-size: 14px;
  font-weight: 200;
}

.align-items-center {
  display: flex; 
  align-items: center;  /*Aligns vertically center */
  justify-content: center; /*Aligns horizontally center */
}
</style>

<style lang="scss">
.vue2leaflet-map{
  &.leaflet-container{
    width: 400px;
    height: 200px;
  }
}
</style>
<style>
.link-white-color {
  color: white;
  }

  button[id^='__BVID__'] {
    padding: 0;
  }

  .dropdown-item {
    padding-bottom: 2px;
    padding-top: 2px;
  }
</style>

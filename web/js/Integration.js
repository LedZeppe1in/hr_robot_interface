 var RBRClient = {ID: null,
                  IDOfRDBEngine: null,
                  ConnectionParameters: 'ws://84.201.129.65:7777',

                  AddressForConnectionToDrools: 'http://84.201.129.65:9999/Drools/RetrieveData.php',

                  CodeOfKnowledgeBase: null,
                  CodeOfDataForReasoningProcess: null,

                  ParentUIControl: {ID: 'WebSocketClientDiv',
                                    jQueryReference: null},

                  ListOfMessagesUIControl: {ID: 'ListOfMessagesUIControl',
                                            jQueryReference: null},
                  ConnectButton: {ID: 'ConnectToWebSocketServerButton',
                                  jQueryReference: null},
                  DisconnectButton: {ID: 'DisconnectFromWebSocketServerButton',
                                     jQueryReference: null},
                  SendDataButton: {ID: 'SendDataToWebSocketServerButton',
                                   jQueryReference: null}};

 function DefaultWebSocketClient()
  {
   if (typeof(WebSocket) == 'undefined')
    {
     console.error('Your browser does not support WebSockets');
     return false;
    }

   if (DefaultWebSocketClient.Executed == true) {return false;}

   DefaultWebSocketClient.Executed = true;

   DefaultWebSocketClient.Reset = function()
    {
     DefaultWebSocketClient.Ready = false;
     DefaultWebSocketClient.Connected = false;
     DefaultWebSocketClient.Connection = undefined;
     DefaultWebSocketClient.CountOfHandlersOfMessages = 0;
     DefaultWebSocketClient.HandlersOfMessages = {};
    }

   DefaultWebSocketClient.Reset();

   DefaultWebSocketClient.RetrieveHandlersOfMessagesByType = function(TypeOfMessages)
    {
     var Result = DefaultWebSocketClient.HandlersOfMessages[TypeOfMessages];
     if ((Result != undefined) &&
         (typeof(Result) == 'object'))
      {
       return Result;
      }
     else
      {
       return false;
      }
    }

   DefaultWebSocketClient.AddNewHandlerOfMessageType = function(TypeOfMessage, HandlerFunction)
    {
     if (typeof(HandlerFunction) != 'function')
      {
       return false;
      }

     DefaultWebSocketClient.CountOfHandlersOfMessages = DefaultWebSocketClient.CountOfHandlersOfMessages + 1;
     var NewIDOfHandler = 'ID-' + DefaultWebSocketClient.CountOfHandlersOfMessages;

     var HandlersOfCurrentTypeOfMessages = DefaultWebSocketClient.RetrieveHandlersOfMessagesByType(TypeOfMessage);
     if (HandlersOfCurrentTypeOfMessages == false)
      {
       HandlersOfCurrentTypeOfMessages = {};
       HandlersOfCurrentTypeOfMessages[NewIDOfHandler] = HandlerFunction;
       DefaultWebSocketClient.HandlersOfMessages[TypeOfMessage] = HandlersOfCurrentTypeOfMessages;
      }
     else
      {
       HandlersOfCurrentTypeOfMessages[NewIDOfHandler] = HandlerFunction;
      }

     return NewIDOfHandler;
    }

   DefaultWebSocketClient.RemoveOnMessageOfTypeEventHandler = function(TypeOfMessage, IDOfHandler)
    {
     var HandlersOfCurrentTypeOfMessages = DefaultWebSocketClient.RetrieveHandlerOfMessagesByType(TypeOfMessages);
     if ((HandlersOfCurrentTypeOfMessages != false) &&
         (HandlersOfCurrentTypeOfMessages[IDOfHandler] != undefined))
      {
       delete HandlersOfCurrentTypeOfMessages[IDOfHandler];
       return true;
      }
     else
      {
       return false;
      }
    }

   DefaultWebSocketClient.Initialize = function()
    {
     if (DefaultWebSocketClient.Connection != undefined)
      {
       delete DefaultWebSocketClient.Connection;
      }

     RBRClient.ConnectButton.jQueryReference.bind('click', function()
      {
       RBRClient.ConnectButton.jQueryReference.prop('disabled', true);

       var WebSocketConnection = new WebSocket(RBRClient.ConnectionParameters);

       WebSocketConnection.onopen = function(Data)
        {
         DefaultWebSocketClient.Connection = WebSocketConnection;
         DefaultWebSocketClient.Connected = true;

         console.log('Подключение установлено...');
        }

       DefaultWebSocketClient.
        AddNewHandlerOfMessageType('IDOfClient',
                                   function(IDOfRecipient, DataOfMessage)
                                    {
                                     RBRClient.ID = DataOfMessage.ID;
                                     console.log('Получен ID: ' + DataOfMessage.ID);

                                     jQuery.post(RBRClient.AddressForConnectionToDrools,
                                                 {DataSource: 'ConnectToDroolsRBRWebSocketTerminal',
                                                  Code: RBRClient.CodeOfKnowledgeBase,
                                                  IDOfWebSocketUser: RBRClient.ID},
                                                 function (data)
                                                  {
                                                   console.log('Запрос ID of Drools RBR Engine отправлен');
                                                  });
                                    });

       DefaultWebSocketClient.
        AddNewHandlerOfMessageType('Initialization',
                                   function(IDOfRecipient, DataOfMessage)
                                    {
                                     RBRClient.IDOfRDBEngine = DataOfMessage.IDOfSender;
                                     console.log('Получен ID of Drools RBR Engine: ' + RBRClient.IDOfRDBEngine);

                                     RBRClient.ListOfMessagesUIControl.jQueryReference.html('');
                                     RBRClient.DisconnectButton.jQueryReference.prop('disabled', false);
                                     RBRClient.SendDataButton.jQueryReference.prop('disabled', false);
                                    });

       DefaultWebSocketClient.
        AddNewHandlerOfMessageType('ResultsOfExecution',
                                   function(IDOfRecipient, DataOfMessage)
                                    {
                                     if (RBRClient.ReasoningProcessStarted != true)
                                      {
                                       return false;
                                      }

                                     RBRClient.
                                      ListOfMessagesUIControl.
                                       jQueryReference.
                                        prepend('Результаты анализа кадра №: ' + RBRClient.IndexOfCurrentFrame + '<br />' + JSON.stringify(DataOfMessage) + '<br /><br />');

                                     RBRClient.IndexOfCurrentFrame = RBRClient.IndexOfCurrentFrame + 1;

                                     if (RBRClient.IndexOfCurrentFrame == RBRClient.DataArrayForReasoningProcess.length)
                                      {
                                       RBRClient.SendDataButton.jQueryReference.prop('disabled', false);
                                       RBRClient.ReasoningProcessStarted = false;
                                       RBRClient.IndexOfCurrentFrame = 0;

                                       return false;
                                      }

                                     RBRClient.SendDataOfCurrentFrame();
                                    });

       DefaultWebSocketClient.
        AddNewHandlerOfMessageType('Status',
                                   function(IDOfRecipient, DataOfMessage)
                                    {
                                     console.log(JSON.stringify(DataOfMessage));
                                    });

       WebSocketConnection.onmessage = function(Message)
        {
         var DataOfMessage = JSON.parse(Message.data);
         if (DataOfMessage.Type != undefined)
          {
           var HandlersOfMessages = DefaultWebSocketClient.RetrieveHandlersOfMessagesByType(DataOfMessage.Type);
           if (HandlersOfMessages != false)
            {
             for (var IDOfHandler in HandlersOfMessages)
              {
               if (typeof(HandlersOfMessages[IDOfHandler]) == 'function')
                {
                 HandlersOfMessages[IDOfHandler](DataOfMessage.ID, DataOfMessage);
                }
              }
            }
           else
            {
             RBRClient.ListOfMessagesUIControl.jQueryReference.prepend(Message.data + '<br /><br />');
            }
          }
         else
          {
           RBRClient.ListOfMessagesUIControl.jQueryReference.prepend(Message.data + '<br /><br />');
          }
        }

       WebSocketConnection.onclose = function(Data)
        {
         delete DefaultWebSocketClient.Connection;
         DefaultWebSocketClient.Connected = false;

         console.log('Отключено... Код: ' + Data.code);
         
         RBRClient.ListOfMessagesUIControl.jQueryReference.prepend('Отключено... Код: ' + Data.code + '<br /><br />');

         RBRClient.ConnectButton.jQueryReference.prop('disabled', false);
         RBRClient.DisconnectButton.jQueryReference.prop('disabled', true);
         RBRClient.SendDataButton.jQueryReference.prop('disabled', true);
        }

       RBRClient.DisconnectButton.jQueryReference.bind('click', function()
        {
         DefaultWebSocketClient.Connection.close();
        });
      });

     RBRClient.SendDataOfCurrentFrame = function()
      {
       console.log('Отправлены данные фрейма: ' + RBRClient.IndexOfCurrentFrame);

       DefaultWebSocketClient.
        SendMessage(RBRClient.IDOfRDBEngine, 
                    'RBR Run', 
                    RBRClient.DataArrayForReasoningProcess[RBRClient.IndexOfCurrentFrame]);
      }

     RBRClient.SendDataButton.jQueryReference.bind('click', function()
      {
       try
        {
         RBRClient.DataArrayForReasoningProcess = JSON.parse(RBRClient.CodeOfDataForReasoningProcess);
         if ((Array.isArray(RBRClient.DataArrayForReasoningProcess) == true) &&
             (RBRClient.DataArrayForReasoningProcess.length > 0))
          {
           RBRClient.SendDataButton.jQueryReference.prop('disabled', true);
           RBRClient.ReasoningProcessStarted = true;
           RBRClient.IndexOfCurrentFrame = 0;

           RBRClient.SendDataOfCurrentFrame();
          }
        }
       catch(E)
        {
         RBRClient.DataArrayForReasoningProcess = null;
         console.log(E);
        }
      });

     DefaultWebSocketClient.Ready = true;
     RBRClient.ConnectButton.jQueryReference.prop('disabled', false);
    }

   DefaultWebSocketClient.SendMessage = function(IDOfRecipient, TypeOfMessage, DataOfMessage)
    {
     if ((DefaultWebSocketClient.Ready == false) ||
         (DefaultWebSocketClient.Connected == false))
      {
       return false;
      }

     var Message = {};
     Message.IDOfRecipient = IDOfRecipient;
     Message.Type = TypeOfMessage;
     Message.Data = DataOfMessage;
     Message.Data.ReturnResultsInHumanOrientedFormat = true

     DefaultWebSocketClient.Connection.send(JSON.stringify(Message));
    }

   return DefaultWebSocketClient;
  }

 function InitializeUserInterface()
  {
   if (InitializeUserInterface.Executed == true) {return false;}
   InitializeUserInterface.Executed = true;

   RBRClient.ParentUIControl.jQueryReference = jQuery('#' + RBRClient.ParentUIControl.ID);

   var ParentUIControl = RBRClient.ParentUIControl.jQueryReference;
   ParentUIControl.html('<div id = "' + RBRClient.ListOfMessagesUIControl.ID + '"></div>' +
                        '<button id = "' + RBRClient.ConnectButton.ID + '">Connect</button>' +
                        '<button id = "' + RBRClient.DisconnectButton.ID + '">Disonnect</button>' +
                        '<button id = "' + RBRClient.SendDataButton.ID + '">Send data</button>');

   RBRClient.ConnectButton.jQueryReference = jQuery('#' + RBRClient.ConnectButton.ID, ParentUIControl);
   RBRClient.DisconnectButton.jQueryReference = jQuery('#' + RBRClient.DisconnectButton.ID, ParentUIControl);
   RBRClient.SendDataButton.jQueryReference = jQuery('#' + RBRClient.SendDataButton.ID, ParentUIControl);
   RBRClient.ListOfMessagesUIControl.jQueryReference = jQuery('#' + RBRClient.ListOfMessagesUIControl.ID, ParentUIControl);
	   
   RBRClient.ConnectButton.jQueryReference.unbind('click');
   RBRClient.DisconnectButton.jQueryReference.unbind('click');
   RBRClient.SendDataButton.jQueryReference.unbind('click');
   
   RBRClient.ConnectButton.jQueryReference.prop('disabled', true);
   RBRClient.DisconnectButton.jQueryReference.prop('disabled', true);
   RBRClient.SendDataButton.jQueryReference.prop('disabled', true);

   RBRClient.CodeOfKnowledgeBase = knowledgeBase;
   RBRClient.CodeOfDataForReasoningProcess = factTemplates;

   // if ((RBRClient.CodeOfKnowledgeBase == undefined) ||
   //     (RBRClient.CodeOfKnowledgeBase == null) ||
   //     (RBRClient.CodeOfKnowledgeBase == ''))
   //  {
   //   RBRClient.CodeOfKnowledgeBase = knowledgeBase;
   //  }

    // if ((RBRClient.CodeOfDataForReasoningProcess == undefined) ||
    //     (RBRClient.CodeOfDataForReasoningProcess == null) ||
    //     (RBRClient.CodeOfDataForReasoningProcess == ''))
    // {
    //  RBRClient.CodeOfDataForReasoningProcess = factTemplates;
    // }

   DefaultWebSocketClient();
   DefaultWebSocketClient.Initialize();
  }

 jQuery(document).ready(function()
  {
   InitializeUserInterface();
  });

 var RBRClient = {ID: null,
                  IDOfRDBEngine: null,
                  ConnectionParameters: 'ws://84.201.129.65:7777',

                  AddressForConnectionToDrools: 'http://84.201.129.65:9999/Drools/RetrieveData.php',

                  CodeOfKnowledgeBase: null,
                  CodeOfDataForReasoningProcess: null,

                  SendingDataProcessStarted: false,
                  SendingDataProcessPaused: false,

                  ParentUIControl: {ID: 'WebSocketClientDiv',
                                    jQueryReference: null},

                  DialogUIControl: {ID: 'ReasoningResultsDialog',
                                    jQueryReference: null,
                                    Parameters: {autoOpen: false,
                                                 title: 'Результаты вывода',
                                                 height: 400,
                                                 width: 575,
                                                 modal: false}},

                  ListOfMessagesUIControl: {ID: 'ListOfMessagesUIControl',
                                            jQueryReference: null},
                  CounterOfFramesUIControl: {ID: 'CounterOfFramesUIControl',
                                             jQueryReference: null},

                  ConnectButton: {ID: 'ConnectToWebSocketServerButton',
                                  jQueryReference: null},
                  DisconnectButton: {ID: 'DisconnectFromWebSocketServerButton',
                                     jQueryReference: null},
                  StartReasoningProcessButton: {ID: 'StartReasoningProcessButton',
                                                jQueryReference: null},
                  ActivatorOfStepByStepMode: {ID: 'ActivatorOfStepByStepMode',
                                              jQueryReference: null},
                  PauseButton: {ID: 'PauseButton',
                                   jQueryReference: null},
                  OneStepButton: {ID: 'OneStepButton',
                                   jQueryReference: null},
                  DataOfFrameGrid: {ID: 'DataOfFrameGrid',
                                    jQueryReference: null,
                                    Ready: false}};

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
                                     RBRClient.CounterOfFramesUIControl.jQueryReference.html('');
                                     RBRClient.DisconnectButton.jQueryReference.prop('disabled', false);
                                     RBRClient.StartReasoningProcessButton.jQueryReference.prop('disabled', false);
                                     RBRClient.PauseButton.jQueryReference.prop('disabled', true);
                                     RBRClient.OneStepButton.jQueryReference.prop('disabled', true);
                                    });

       DefaultWebSocketClient.
        AddNewHandlerOfMessageType('ResultsOfExecution',
                                   function(IDOfRecipient, DataOfMessage)
                                    {
                                     if (RBRClient.SendingDataProcessStarted != true)
                                      {
                                       return false;
                                      }

                                     if (RBRClient.DialogUIControl.jQueryReference.dialog('isOpen') == false)
                                      {
                                       RBRClient.DialogUIControl.jQueryReference.dialog('open');
                                      }

                                     RBRClient.PrintRBRServerResponce(DataOfMessage);

                                     if (RBRClient.IndexOfCurrentFrame == RBRClient.DataArrayForReasoningProcess.length)
                                      {
                                       RBRClient.StartReasoningProcessButton.jQueryReference.prop('disabled', false);
                                       RBRClient.PauseButton.jQueryReference.prop('disabled', true);
                                       RBRClient.OneStepButton.jQueryReference.prop('disabled', true);
                                       RBRClient.SendingDataProcessStarted = false;
                                       RBRClient.IndexOfCurrentFrame = 0;

                                       return false;
                                      }

                                     if (RBRClient.SendingDataProcessPaused == false)
                                      {
                                       RBRClient.SendDataOfCurrentFrame(null);
                                      }
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

         DefaultWebSocketClient.Reset();

         console.log('Отключено... Код: ' + Data.code);
         
         RBRClient.ListOfMessagesUIControl.jQueryReference.prepend('Отключено... Код: ' + Data.code + '<br /><br />');

         RBRClient.ConnectButton.jQueryReference.prop('disabled', false);
         RBRClient.DisconnectButton.jQueryReference.prop('disabled', true);
         RBRClient.StartReasoningProcessButton.jQueryReference.prop('disabled', true);
         RBRClient.PauseButton.jQueryReference.prop('disabled', true);
         RBRClient.OneStepButton.jQueryReference.prop('disabled', true);
        }

       RBRClient.DisconnectButton.jQueryReference.bind('click', function()
        {
         DefaultWebSocketClient.Connection.close();
        });
      });

     RBRClient.SendDataOfCurrentFrame = function(DataOfCurrentFrame)
      {
       console.log('Отправлены данные фрейма: ' + RBRClient.IndexOfCurrentFrame);

       if (RBRClient.ActivatorOfStepByStepMode.jQueryReference.parent().css('display') != 'none')
        {
         RBRClient.ActivatorOfStepByStepMode.jQueryReference.parent().css('display', 'none');
         if (RBRClient.ActivatorOfStepByStepMode.jQueryReference.prop('checked') == true)
          {
           RBRClient.PauseButton.jQueryReference.click();
          }
        }

       if ((DataOfCurrentFrame == undefined) ||
           (DataOfCurrentFrame == null) ||
           (Array.isArray(DataOfCurrentFrame) == false))
        {
         DataOfCurrentFrame = RBRClient.DataArrayForReasoningProcess[RBRClient.IndexOfCurrentFrame];
        }

       DefaultWebSocketClient.SendMessage(RBRClient.IDOfRDBEngine, 'RBR Run', DataOfCurrentFrame);
       RBRClient.IndexOfCurrentFrame = RBRClient.IndexOfCurrentFrame + 1;
      }

     RBRClient.PrintRBRServerResponce = function(ResponceData)
      {
       RBRClient.CounterOfFramesUIControl.jQueryReference.html(RBRClient.IndexOfCurrentFrame);
       RBRClient.CounterOfFramesUIControl.jQueryReference.prop('title', '№ текущего кадра: ' + RBRClient.IndexOfCurrentFrame);

       var FiredRules = ResponceData.Data.FiredRules;
       var FiredRulesAsText = '';
       if (FiredRules.length > 0)
        {
         FiredRulesAsText = JSON.stringify(FiredRules);
        }

       var ContentOfWorkingMemory = ResponceData.Data.ContentOfWorkingMemory;
       var ContentOfWorkingMemoryAsText = '';
       if (ContentOfWorkingMemory.length > 0)
        {
         ContentOfWorkingMemoryAsText = JSON.stringify(ContentOfWorkingMemory);
        }

       if (FiredRulesAsText != '')
        {
         FiredRulesAsText = 'Сработали следующие правила: <br />' + JSON.stringify(FiredRules);
        }

       if (ContentOfWorkingMemoryAsText != '')
        {
         ContentOfWorkingMemoryAsText = 'Содержимое рабочей памяти: ' + JSON.stringify(ContentOfWorkingMemory);
        }

       if ((FiredRulesAsText != '') || 
           (ContentOfWorkingMemoryAsText != ''))
        {
         var Result = '';
         if (FiredRulesAsText != '')
          {
           Result = FiredRulesAsText;
          }

         if (Result != '')
          {
           Result = Result + '<br />';
          }

         if (ContentOfWorkingMemoryAsText != '')
          {
           Result = Result + ContentOfWorkingMemoryAsText;
          }

         RBRClient.ListOfMessagesUIControl.jQueryReference.prepend('Результаты анализа кадра №: ' + Result + '<br /><br />');
        }
      }

     RBRClient.StartReasoningProcessButton.jQueryReference.bind('click', function()
      {
       try
        {
         RBRClient.DataArrayForReasoningProcess = JSON.parse(RBRClient.CodeOfDataForReasoningProcess);
         if ((Array.isArray(RBRClient.DataArrayForReasoningProcess) == true) &&
             (RBRClient.DataArrayForReasoningProcess.length > 0))
          {
           RBRClient.StartReasoningProcessButton.jQueryReference.prop('disabled', true);
           RBRClient.PauseButton.jQueryReference.prop('disabled', false);
           RBRClient.OneStepButton.jQueryReference.prop('disabled', false);
           RBRClient.SendingDataProcessStarted = true;
           RBRClient.IndexOfCurrentFrame = 0;

           if (RBRClient.DataOfFrameGrid.Ready == true)
            {
             RBRClient.DataOfFrameGrid.jQueryReference.jqGrid('GridUnload');
             RBRClient.DataOfFrameGrid.Ready = false;
            }

           RBRClient.DialogUIControl.jQueryReference.dialog('open');

           RBRClient.SendDataOfCurrentFrame(null);
          }
        }
       catch(E)
        {
         RBRClient.DataArrayForReasoningProcess = null;
         console.log(E);
        }
      });
      
     RBRClient.PauseButton.jQueryReference.bind('click', function()
      {
       try
        {
         if (RBRClient.SendingDataProcessPaused == false)
          {
           RBRClient.SendingDataProcessPaused = true;
           RBRClient.PauseButton.jQueryReference.html('Продолжить');

           if (RBRClient.DataOfFrameGrid.Ready == false)
            {
             InitializeUserInterface.CreateDataGrid(RBRClient.ParentUIControl.jQueryReference);
            }

           InitializeUserInterface.SendDataToGrid(RBRClient.DataArrayForReasoningProcess[RBRClient.IndexOfCurrentFrame]);
          }
         else
          {
           RBRClient.SendingDataProcessPaused = false;
           RBRClient.PauseButton.jQueryReference.html('Приостановить');

           var DataArrayForReasoningProcess = InitializeUserInterface.RetrieveDataFromGrid();
           RBRClient.DataOfFrameGrid.jQueryReference.jqGrid('GridUnload');
           RBRClient.DataOfFrameGrid.Ready = false;

           RBRClient.SendDataOfCurrentFrame(DataArrayForReasoningProcess);
          }
        }
       catch(E)
        {
         console.log(E);
        }
      });
      
     RBRClient.OneStepButton.jQueryReference.bind('click', function()
      {
       try
        {
         var DataArrayForReasoningProcess = InitializeUserInterface.RetrieveDataFromGrid();
         RBRClient.DataOfFrameGrid.jQueryReference.jqGrid('GridUnload');
         RBRClient.DataOfFrameGrid.Ready = false;

         RBRClient.SendDataOfCurrentFrame(DataArrayForReasoningProcess);

         InitializeUserInterface.CreateDataGrid(RBRClient.ParentUIControl.jQueryReference);
         InitializeUserInterface.SendDataToGrid(RBRClient.DataArrayForReasoningProcess[RBRClient.IndexOfCurrentFrame]);
        }
       catch(E)
        {
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
     Message.Data = {InitialDataOfRBRProcess: DataOfMessage,
                     ReturnResultsInHumanOrientedFormat: true};

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
   ParentUIControl.html('<div id = "' + RBRClient.DialogUIControl.ID + '" style = "font-size: 80%;">' + 
                         '<div id = "' + RBRClient.ListOfMessagesUIControl.ID + '"></div>' + 
                         '<div id = "' + RBRClient.CounterOfFramesUIControl.ID + '" style = "border: 1px solid blue; font-size: 120%; position: absolute; top: 3px; right: 5px;"></div>' + 
                        '</div>' +
                        '<button id = "' + RBRClient.ConnectButton.ID + '">Подключиться</button>' +
                        '<button id = "' + RBRClient.DisconnectButton.ID + '">Отключиться</button>' +
                        '<button id = "' + RBRClient.StartReasoningProcessButton.ID + '">Начать процесс покадровой интерпретации</button>' +
                        '<span style = "font-size: 150%;"><input id = "' + RBRClient.ActivatorOfStepByStepMode.ID + '" type = "checkbox"/>В пошаговом режиме&nbsp&nbsp</span>' +
                        '<button id = "' + RBRClient.PauseButton.ID + '">Приостановить</button>' +
                        '<button id = "' + RBRClient.OneStepButton.ID + '">Интерпретировать данные текущего кадра</button>');

   RBRClient.ListOfMessagesUIControl.jQueryReference = jQuery('#' + RBRClient.ListOfMessagesUIControl.ID, ParentUIControl);
   RBRClient.CounterOfFramesUIControl.jQueryReference = jQuery('#' + RBRClient.CounterOfFramesUIControl.ID, ParentUIControl);

   RBRClient.DialogUIControl.jQueryReference = jQuery('#' + RBRClient.DialogUIControl.ID, ParentUIControl);
   RBRClient.DialogUIControl.jQueryReference.dialog(RBRClient.DialogUIControl.Parameters);

   RBRClient.ConnectButton.jQueryReference = jQuery('#' + RBRClient.ConnectButton.ID, ParentUIControl);
   RBRClient.DisconnectButton.jQueryReference = jQuery('#' + RBRClient.DisconnectButton.ID, ParentUIControl);
   RBRClient.StartReasoningProcessButton.jQueryReference = jQuery('#' + RBRClient.StartReasoningProcessButton.ID, ParentUIControl);
   RBRClient.ActivatorOfStepByStepMode.jQueryReference = jQuery('#' + RBRClient.ActivatorOfStepByStepMode.ID, ParentUIControl);
   RBRClient.PauseButton.jQueryReference = jQuery('#' + RBRClient.PauseButton.ID, ParentUIControl);
   RBRClient.OneStepButton.jQueryReference = jQuery('#' + RBRClient.OneStepButton.ID, ParentUIControl);

   RBRClient.ConnectButton.jQueryReference.unbind('click');
   RBRClient.DisconnectButton.jQueryReference.unbind('click');
   RBRClient.StartReasoningProcessButton.jQueryReference.unbind('click');
   RBRClient.PauseButton.jQueryReference.unbind('click');
   RBRClient.OneStepButton.jQueryReference.unbind('click');

   RBRClient.ConnectButton.jQueryReference.prop('disabled', true);
   RBRClient.DisconnectButton.jQueryReference.prop('disabled', true);
   RBRClient.StartReasoningProcessButton.jQueryReference.prop('disabled', true);
   RBRClient.PauseButton.jQueryReference.prop('disabled', true);
   RBRClient.OneStepButton.jQueryReference.prop('disabled', true);

   RBRClient.CodeOfKnowledgeBase = knowledgeBase;
   RBRClient.CodeOfDataForReasoningProcess = facts;

   InitializeUserInterface.CreateDataGrid = function(jQueryReferenceOfParentUIControl)
    {
     var ReferenceToCurrentFunction = InitializeUserInterface.CreateDataGrid;

     jQueryReferenceOfParentUIControl.append('<table id = "' + RBRClient.DataOfFrameGrid.ID + '" class = "scroll"></table>');
     RBRClient.DataOfFrameGrid.jQueryReference = jQuery('#' + RBRClient.DataOfFrameGrid.ID, jQueryReferenceOfParentUIControl);

     var ParametersOfGrid = {colModel: [{name: 'ID',
                                         key: true,
                                         hidden: true},
                                        {name: 'NameOfTemplate',
                                         hidden: true},
                                        {name: 's861',
                                         label: 'Часть лица',
                                         width: 150,
                                         editable: true},
                                        {name: 's862',
                                         label: 'Тип изменения признака',
                                         width: 200,
                                         editable: true},
                                        {name: 's863',
                                         label: 'Направление изменения',
                                         editable: true},
                                        {name: 's864',
                                         label: 'Величина',
                                         editable: true},
                                        {name: 's869',
                                         label: 'Номер конечного кадра'},
                                        {name: 's870',
                                         label: 'Номер начального кадра'},
                                        {name: 's871',
                                         label: 'Количество кадров'},
                                        {name: 's874',
                                         label: 'Номер кадра'}],
                             datatype: 'local',
                             toppager: true,
                             autowidth: false,
                             height: 550,
                             width: '95%',
                             cellEdit: true,
                             editurl: 'clientArray',
                             cellsubmit: 'clientArray',
                             beforeEditCell: function(rowid, cellname, value, iRow, iCol)
                              {
                               ReferenceToCurrentFunction.SelectedCell = {IndexOfRow: iRow,
                                                                          IndexOfColumn: iCol};
                              },
                             afterRestoreCell: function(rowid, value, iRow, iCol)
                              {
                              },
                             afterSaveCell: function(rowid, cellname, value, iRow, iCol)
                              {
                              }};

     RBRClient.DataOfFrameGrid.Parameters = ParametersOfGrid;

     var DataGrid = RBRClient.DataOfFrameGrid.jQueryReference;
     DataGrid.jqGrid(ParametersOfGrid);
     DataGrid.jqGrid('navGrid',
                     RBRClient.DataOfFrameGrid.ID + '_toppager',
                     {add: true, edit: false, view: false, del: false, search: false, refresh: false},
                     {},
                     {},
                     {},
                     {});

     DataGrid.jqGrid('gridResize',
                     {minWidth: 350,
                      maxWidth: 800,
                      minHeight: 80,
                      maxHeight: 350});

     RBRClient.DataOfFrameGrid.Ready = true;
    }

   InitializeUserInterface.SendDataToGrid = function(DataOfFrame)
    {
     var DataGrid = RBRClient.DataOfFrameGrid.jQueryReference;
     if (DataGrid == null)
      {
       return false;
      }

     var ParametersOfGrid = RBRClient.DataOfFrameGrid.Parameters;
     var RowData;
     var ObjectReference;
     for (var i = 0; i < DataOfFrame.length; i++)
      {
       RowData = {};
       RowData[ParametersOfGrid.colModel[0].name] = i;

       ObjectReference = DataOfFrame[i];
       for (var NameOfProperty in ObjectReference)
        {
         RowData[NameOfProperty] = ObjectReference[NameOfProperty];
        }

       DataGrid.jqGrid('addRowData', i + 1, RowData);
      }

     return true;
    }

   InitializeUserInterface.RetrieveDataFromGrid = function()
    {
     var DataGrid = RBRClient.DataOfFrameGrid.jQueryReference;
     if (DataGrid == null)
      {
       return false;
      }

     var ModelOfColumns = DataGrid.jqGrid('getGridParam', 'colModel');
     var Result = [];
     var IDsOfData = DataGrid.jqGrid('getDataIDs');
     var DataOfRow;
     var ObjectReference;
     for (var i = 0; i < IDsOfData.length; i++)
      {
       DataOfRow = DataGrid.jqGrid('getLocalRow', IDsOfData[i]);
       ObjectReference = {};
       for (var j = 1; j < ModelOfColumns.length; j++)
        {
         ObjectReference[ModelOfColumns[j].name] = DataOfRow[ModelOfColumns[j].name];
        }

       Result[Result.length] = ObjectReference;
      }
      
     return Result;
    }

   DefaultWebSocketClient();
   DefaultWebSocketClient.Initialize();
  }

 jQuery(document).ready(function()
  {
   InitializeUserInterface();
  });

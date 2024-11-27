<script src="https://cdn.onesignal.com/sdks/OneSignalSDK.js" async='async'></script>
<script>
    // Make sure OneSignal Init code is on this page
    function bindEvent(element, eventName, eventHandler) {
        element.addEventListener(eventName, eventHandler, false);
    }

    // Accessed within iframe on subdomain.site
    // Sends a message to mainsite
    var sendMessage = function (msg) {
        console.log(`2 Mainsite is Sending Message to subdomain.site ${msg}`)
        // postMessage: https://developer.mozilla.org/en-US/docs/Web/API/Window/postMessage
        window.parent.postMessage(msg, 'https://froiden.worksuite-saas.test');
    };
    bindEvent(window, 'load', function (e) {
        OneSignal.push(function () {
            OneSignal.isPushNotificationsEnabled(function (isEnabled) {
                console.log(`1 subdomain.site iframe checking subscription from mainsite, it is ${isEnabled}`)
                sendMessage(isEnabled)
            });
        });
    });
</script>

@if($pushSetting->status == 'active')
    <link rel="manifest" href="{{ asset('manifest.json') }}"/>
    <script src="https://cdn.onesignal.com/sdks/OneSignalSDK.js" async='async'></script>
    <script>
        var OneSignal = window.OneSignal || [];
        OneSignal.push(function () {
            OneSignal.init({
                appId: "{{ $pushSetting->onesignal_app_id }}",
                autoRegister: false,
                notifyButton: {
                    enable: false,
                },
                promptOptions: {
                    /* actionMessage limited to 90 characters */
                    actionMessage: "We'd like to show you notifications for the latest news and updates.",
                    /* acceptButtonText limited to 15 characters */
                    acceptButtonText: "ALLOW",
                    /* cancelButtonText limited to 15 characters */
                    cancelButtonText: "NO THANKS"
                }
            });
            OneSignal.on('subscriptionChange', function (isSubscribed) {
                console.log("The user's subscription state is now:", isSubscribed);
            });


            if (Notification.permission === "granted") {
                // Automatically subscribe user if deleted cookies and browser shows "Allow"
                OneSignal.getUserId()
                    .then(function (userId) {
                        if (!userId) {
                            OneSignal.registerForPushNotifications();
                        } else {
                            let db_onesignal_id = '{{ $user->onesignal_player_id }}';

                            if (db_onesignal_id == null || db_onesignal_id !== userId) { //update onesignal ID if it is new
                                updateOnesignalPlayerId(userId);
                            }
                        }
                    })
            } else {
                OneSignal.isPushNotificationsEnabled(function (isEnabled) {
                    if (isEnabled) {
                        console.log("Push notifications are enabled! - 2    ");
                        // console.log("unsubscribe");
                        // OneSignal.setSubscription(false);
                    } else {
                        console.log("Push notifications are not enabled yet. - 2");
                        // OneSignal.showHttpPrompt();
                        // OneSignal.registerForPushNotifications({
                        //         modalPrompt: true
                        // });
                    }

                    OneSignal.getUserId(function (userId) {
                        console.log("OneSignal User ID:", userId);
                        // (Output) OneSignal User ID: 270a35cd-4dda-4b3f-b04e-41d7463a2316
                        let db_onesignal_id = '{{ $user->onesignal_player_id }}';
                        console.log('database id : ' + db_onesignal_id);

                        if (db_onesignal_id == null || db_onesignal_id !== userId) { //update onesignal ID if it is new
                            updateOnesignalPlayerId(userId);
                        }


                    });


                    OneSignal.showHttpPrompt();
                });

            }
        });
    </script>
@endif

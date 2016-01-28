<?php

$user_guid = (int) get_input('guid');

$owner = elgg_get_logged_in_user_entity();

$site = elgg_get_site_entity();

$dbprefix = elgg_get_config('dbprefix');

$users = array((int)$user_guid, (int)$owner->guid);
sort($users);
$chat_id = json_encode($users);

$chats = elgg_get_entities_from_metadata(array(
	'type' => 'object',
	'subtype' => 'chat',
	// The site is used as the owner because this is an equal chat between
	// two parties, and therefore it cannot be owner by either user.
	'owner_guid' => $site->guid,
	// The correct chat is found based on an unique json encoded array
	// that contains GUIDs of both users in an ascending order.
	'metadata_name_value_pairs' => array(
		'name' => 'chat_id',
		'value' => $chat_id,
	),
));

$messages = '';

if (empty($chats)) {
	$chat = new ElggChat();
	$chat->owner_guid = $site->guid;
	$chat->container_guid = $site->guid;
	$chat->access_id = ACCESS_LOGGED_IN;
	$chat->title = 'Test';
	$chat->chat_id = $chat_id;

	if ($chat->save()) {
		$chat->addMember($owner->guid);
		$chat->addMember($user_guid);
	} else {
		register_error(elgg_echo('chat:error:cannot_save'));
		return;
	}
} else {
	$chat =  $chats[0];

	$messages = elgg_view('live_chat/messages', array(
		'entity' => $chat,
		'user' => $owner,
	));
}

echo json_encode((object) array(
	'chat_guid' => $chat->guid,
	'messages' => $messages,
));

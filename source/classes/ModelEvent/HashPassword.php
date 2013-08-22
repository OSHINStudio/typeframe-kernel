<?php
class ModelEvent_HashPassword implements Event_ObserverInterface {
	public function update($record) {
		if ($record['password']) {
			/*$salt = Bam_Functions::RandomId();
			$type = 'sha1';
			$hash = sha1("{$record['password']}{$salt}");
			$record['passhash'] = $hash;
			$record['salt'] = $salt;
			$record['hashtype'] = $type;*/
			$record['auth'] = 'Hash';
			$record['hashtype'] = 'bcrypt';
			$prefix = '$2y$';
			$num = str_pad((string)rand(4, 10), 2, '0', STR_PAD_LEFT) . '$';
			$chars = Bam_Functions::RandomId(22);
			$record['salt'] = "{$prefix}{$num}{$chars}";
			$record['passhash'] = crypt($record['password'], $record['salt']);
		}
	}
}

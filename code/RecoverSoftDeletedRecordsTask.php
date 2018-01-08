<?php

namespace LeKoala\SoftDelete\Dev;

use LeKoala\SoftDelete\ORM\SoftDeletable;
use SilverStripe\Dev\BuildTask;
use SilverStripe\ORM\DB;

/**
 * @author Koala
 */
class RecoverSoftDeletedRecordsTask extends BuildTask
{
    /**
     * @var string
     */
    protected $title = 'Recover Soft Deleted Records';
    /**
     * @var string
     */
    protected $description = 'Helps you to track and potentially recover any soft deleted record';

    /**
     * @param \SilverStripe\Control\HTTPRequest $request
     */
    public function run($request)
    {
        $classes = SoftDeletable::listSoftDeletableClasses();

        $selectedClass = $request->getVar('class');
        $recover = $request->getVar('recover');

        if (!$selectedClass) {
            DB::alteration_message("Please choose any of the following class and pass it as 'class' in the url");
            foreach ($classes as $cl) {
                DB::alteration_message($cl);
            }

            return;
        }

        if (!in_array($selectedClass, $classes)) {
            DB::alteration_message("$selectedClass is not valid", "error");

            return;
        }

        $toRecover = array();
        if ($recover) {
            if ($recover == 'all') {

            } else {
                $toRecover = array_map('trim', explode(',', $recover));
            }
        }

        SoftDeletable::$disable = true;
        $records = $selectedClass::get()->where('Deleted IS NOT NULL');
        if (!$records->count()) {
            DB::alteration_message("No soft deleted records");
        }
        foreach ($records as $record) {
            if ($recover == 'all' || in_array($record->ID, $toRecover)) {
                $record->undoDelete();
                DB::alteration_message(
                    $record->getTitle()." (#".$record->ID.") has been recovered",
                    'repaired'
                );
            } else {
                DB::alteration_message(
                    $record->getTitle(
                    )." (#".$record->ID.") has been deleted at ".$record->Deleted.' by '.$record->DeletedBy()->Title
                );
            }
        }
        if ($recover) {
            DB::alteration_message("Recovery complete");
        } else {
            DB::alteration_message(
                "Recover all of of list of records by passing ?recover=all or ?recover=id,id2,id3 in the url"
            );
        }
    }
}

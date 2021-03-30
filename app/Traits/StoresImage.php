<?php

namespace App\Traits;

use Illuminate\Http\Request;


trait StoresImage{

    use GeneratesCodes;
    /**
     * Does very basic image validity checking and stores it. Redirects back if somethings wrong.
     * @Notice: This is not an alternative to the model validation for this field.
     *
     * @param Request $request
     * @return $this|false|string
     */
    public function verifyAndStoreImage( Request $request, $fieldname = 'image', $modifier='' , $directory = 'unknown' , $model = null, $filename = null )
    {
        if( $request->hasFile( $fieldname)) {
            if (!$request->file($fieldname)->isValid()) {
                return redirect()->back()->withInput();
            }

            if($model != null && $filename != null){
                $existence_file = public_path($filename);
                if(file_exists($existence_file)) {
                    unlink($existence_file);
                }
            }

            $image_name = $modifier."_".$this->generateRandomUniqueId($modifier);
            $image_name = $this->slug($image_name,'');
            $image_name = $image_name.".".$request->file($fieldname)->getClientOriginalExtension();
            $image = $request->file($fieldname)->move('image/' . $directory. '/', $image_name);
            
            return $image->getPathName();
        }
        return $model ? $model->$fieldname : null;
    }
}
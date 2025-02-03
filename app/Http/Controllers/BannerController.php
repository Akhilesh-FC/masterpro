<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Slider;
use Illuminate\Support\Facades\Storage;
use  Illuminate\Support\Facades\DB;

class BannerController extends Controller
{
//     public function index()
//     {
		
// 		 $banner = DB::select("SELECT * FROM `sliders` WHERE `status` = 1");

//         return view('banner.index', compact('banner'));
//     }
    

public function index()
{
    $banner = Slider::where('status', 1)->get();

    return view('banner.index', compact('banner'));
}
    
public function banner_store(Request $request)
{
    try {
        // Validate the incoming request data
        // $request->validate([
        //     'image' => 'required|image',
        //     'title' => 'required',
        //     'activity_image' => 'required|image'
        // ]);

        // Get the uploaded files
        $imageFile = $request->file('image');
        //$activityImageFile = $request->file('activity_image');
        $title = $request->title;

        // Define the destination path within the public directory
        $destinationPath = 'uploads/sliders';

        // Create unique filenames for the images
        $imageFileName = time() . '_' . $imageFile->getClientOriginalName();
        //$activityImageFileName = time() . '_activity_' . $activityImageFile->getClientOriginalName();

        // Move the files to the desired location in the public directory
        $imageFile->move(public_path($destinationPath), $imageFileName);
        //$activityImageFile->move(public_path($destinationPath), $activityImageFileName);

        // Generate the URLs for the stored images
        $imageURL = url($destinationPath . '/' . $imageFileName);
        //$activityImageURL = url($destinationPath . '/' . $activityImageFileName);

        // Prepare the data to be inserted into the database
        $data = [
            'image' => $imageURL,
            'title' => $title,
            //'activity_image' => $activityImageURL,
            'status' => 1,
        ];

        // Create a new banner record in the database
        Slider::create($data);

        // Redirect to the 'banner' route with a success message
        return redirect()->route('banner')->with('success', 'Banner added successfully');
    } catch (\Exception $e) {
        // Handle any exceptions, such as database errors or file upload failures
        return redirect()->back()->with('error', 'Error: ' . $e->getMessage())->withInput();
    }
}



 public function banner_update(Request $request, $id)
{
    // Validate the incoming request data
    // $request->validate([
    //     'title' => 'required|string|max:255',
    //     'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
    //     'activity_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
    // ]);

    // Find the banner by ID or fail if not found
    $banner = Slider::findOrFail($id);

    // Initialize the data array with the title
    $data = [
        'title' => $request->title,
    ];

    // Check if the image is present in the request
    if ($request->file('image')) {
        $file = $request->file('image');
        
        // Define the destination path within the public directory
        $destinationPath = 'uploads/sliders';

        // Create a unique filename for the image
        $fileName = time() . '_' . $file->getClientOriginalName();

        // Move the file to the desired location in the public directory
        $file->move(public_path($destinationPath), $fileName);

        // Generate the URL for the stored image
        $imageURL = url($destinationPath . '/' . $fileName);

        // Add the image URL to the data array
        $data['image'] = $imageURL;
    }

    // Check if the activity_image is present in the request
    // if ($request->file('activity_image')) {
    //     $file = $request->file('activity_image');
        
    //     // Define the destination path within the public directory
    //     $destinationPath = 'uploads/activity_images';

    //     // Create a unique filename for the activity_image
    //     $fileName = time() . '_' . $file->getClientOriginalName();

    //     // Move the file to the desired location in the public directory
    //     $file->move(public_path($destinationPath), $fileName);

    //     // Generate the URL for the stored activity_image
    //     $activityImageURL = url($destinationPath . '/' . $fileName);

    //     // Add the activity_image URL to the data array
    //     $data['activity_image'] = $activityImageURL;
    // }

    // Update the banner with the new data
    $banner->update($data);

    // Redirect to the banner route
    return redirect()->route('banner');
}


     
     
      public function banner_delete($id)
    {
    
      $banner = Slider::find($id);
      $banner->delete();
      return redirect()->route('banner')
        ->with('success', 'Banner deleted successfully');
    }

 
}

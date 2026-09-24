    public function bulkDestroy(\Illuminate\Http\Request $request)
    {
        $ids = $request->input('ids');
        if (empty($ids)) {
            return back()->with('error', 'No locations selected.');
        }

        try {
            LocationDataCenter::whereIn('id', $ids)->delete();
            return redirect('admin/locations')->with('success', 'Selected locations deleted successfully.');
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() == '23000') {
                return back()->with('error', 'Cannot delete selected locations because they have active associated resources (e.g., interconnections).');
            }
            throw $e;
        }
    }

    public function destroy(string $id)
    {
        try {
            $location = LocationDataCenter::findOrFail($id);
            $location->delete();
            return redirect('admin/locations')->with('success', 'Location deleted successfully.');
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() == '23000') {
                return back()->with('error', 'Cannot delete this location because it has active associated resources (e.g., interconnections).');
            }
            throw $e;
        }
    }

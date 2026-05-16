import { serve } from "https://deno.land/std@0.168.0/http/server.ts";
import { createClient } from "https://esm.sh/@supabase/supabase-js@2.7.1";

const corsHeaders = {
  "Access-Control-Allow-Origin": "*",
  "Access-Control-Allow-Headers": "authorization, x-client-info, apikey, content-type",
};

serve(async (req: Request) => {
  if (req.method === "OPTIONS") {
    return new Response("ok", { headers: corsHeaders });
  }

  try {
    const supabaseUrl = Deno.env.get("SUPABASE_URL") ?? "";
    const supabaseKey = Deno.env.get("SUPABASE_ANON_KEY") ?? "";
    const supabase = createClient(supabaseUrl, supabaseKey, {
      global: { headers: { Authorization: req.headers.get("Authorization")! } },
    });

    const { algorithm, params } = await req.json();
    let data, error;

    // Route to the appropriate algorithm
    switch (algorithm) {
      case "match_mentors":
        ({ data, error } = await supabase.rpc("match_mentors", params));
        break;
      case "optimize_schedule":
        ({ data, error } = await supabase.rpc("optimize_schedule", params));
        break;
      case "calculate_trust_score":
        ({ data, error } = await supabase.rpc("calculate_trust_score"));
        break;
      case "recommend_mentors":
        ({ data, error } = await supabase.rpc("recommend_mentors", params));
        break;
      default:
        throw new Error("Invalid algorithm requested");
    }

    if (error) throw error;

    return new Response(JSON.stringify({ result: data }), {
      headers: { ...corsHeaders, "Content-Type": "application/json" },
      status: 200,
    });
  } catch (error: any) {
    return new Response(JSON.stringify({ error: error?.message || "Unknown error" }), {
      headers: { ...corsHeaders, "Content-Type": "application/json" },
      status: 400,
    });
  }
});
